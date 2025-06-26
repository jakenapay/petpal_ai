<?php 
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PetModel;
use App\Models\PetStatusModel;

class DecayApply extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'decay:apply';
    protected $description = 'Applies decay to pet attributes based on time elapsed.';
    public function run(array $params)
    {
        date_default_timezone_set('Asia/Manila');
        $now = new \DateTime();

        $petModel = new PetModel();
        $petStatusModel = new PetStatusModel();
        $pets = $petModel->findAll();
        if (empty($pets)) {
            CLI::write('No pets found to apply decay.', 'yellow');
            return;
        }
        //match the pets with their status
        $petStatuses = $petStatusModel->findAll();
        $pets = array_map(function($pet) use ($petStatuses) {
            $status = array_filter($petStatuses, function($s) use ($pet) {
                return $s['pet_id'] === $pet['pet_id'];
            });
            $pet['status'] = !empty($status) ? reset($status) : null;
            return $pet;
        }, $pets);
        if (empty($pets)) {
            CLI::write('No pets found to apply decay.', 'yellow');
            return;
        }

        //log the pets
        CLI::write('Applying decay to pets...', 'green');
        foreach ($pets as $pet) {
            $status = $pet['status'];
            if (!$status) {
                continue; // skip pets with no status
            }
            $lastDecayStr = $status['last_hunger_update'] ?? $status['last_status_calculation'];

            if (!$lastDecayStr) {
                continue; // skip if no timestamp exists
            }

            $lastDecay = new \DateTime($lastDecayStr);
            $minutes = ($now->getTimestamp() - $lastDecay->getTimestamp()) / 60;

            $decayRate = 1.67 / 5; // e.g., ~0.33 per minute
            $decayAmount = floor($minutes * $decayRate);

            if ($decayAmount > 0) {
                $newHunger = max(0, $status['hunger_level'] - $decayAmount);
                $newHappiness = max(0, $status['happiness_level'] - $decayAmount);
                $newEnergy = min(100, max(0, $status['energy_level'] + $decayAmount));
                $newCleanliness = max(0, $status['cleanliness_level'] - $decayAmount);
                $newStress = max(0, $status['stress_level'] - $decayAmount);
                $newHealth = $status['health_level'];
                $newSicknessSeverity = $status['sickness_severity'];
                

                //check if the pet is sick
                $is_sick = $this->sickCheck($status);
                $is_sick = $this->healthCheck($status);
                if ($is_sick === 1) {
                    $severityMultiplier = $this->severityCheck($status);
                    $newHunger = max(0, $status['hunger_level'] - $decayAmount * $severityMultiplier);
                    $newHappiness = max(0, $status['happiness_level'] - $decayAmount * $severityMultiplier);
                    $newEnergy = min(100, max(0, $status['energy_level'] - $decayAmount * $severityMultiplier));
                    $newCleanliness = max(0, $status['cleanliness_level'] - $decayAmount * $severityMultiplier);
                    $newHealth = max(0, $status['health_level'] - $decayAmount * $severityMultiplier);
                    $newStress = max(0, $status['stress_level'] - $decayAmount * $severityMultiplier);
                    $newSicknessSeverity = max(0, $status['sickness_severity'] + $decayAmount * $severityMultiplier);
                }

                $petStatusModel->update($status['status_id'], [
                    'hunger_level' => $newHunger,
                    'happiness_level' => $newHappiness,
                    'energy_level'=> $newEnergy,
                    'cleanliness_level' => $newCleanliness,
                    'stress_level' => $newStress,
                    'health_level' => $newHealth,
                    'current_mood' => $this->moodChange($status),
                    'is_sick' => $is_sick,
                    'sickness_severity' => $newSicknessSeverity,
                    'last_happiness_update' => $now->format('Y-m-d H:i:s'),
                    'last_energy_update' => $now->format('Y-m-d H:i:s'),
                    'last_cleanliness_update' => $now->format('Y-m-d H:i:s'),
                    'last_health_update' => $now->format('Y-m-d H:i:s'),
                    'last_hunger_update' => $now->format('Y-m-d H:i:s'),
                    'last_status_calculation' => $now->format('Y-m-d H:i:s')
                ]);



                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} hunger", 'green');
                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} happiness", 'green');
                CLI::write("Pet ID {$pet['pet_id']} decayed: +{$decayAmount} energy", 'green');
                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} cleanliness", 'green');
                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} stress", 'green');
                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} health", 'green');

                
            }
        }
    }

    public function moodChange($status)
    {
        $hungerLevel = $status['hunger_level'];
        $happinessLevel = $status['happiness_level'];
        $energyLevel = $status['energy_level'];
        $cleanlinessLevel = $status['cleanliness_level'];
        $stressLevel = $status['stress_level'];
        $healthLevel = $status['health_level'];
        $currentMood = $status['current_mood'];

        if ($healthLevel <= 30) {
            $currentMood = 'ill';
        } elseif ($hungerLevel <= 30) {
            $currentMood = 'hungry';
        } elseif ($cleanlinessLevel <= 30) {
            $currentMood = 'dirty';
        } elseif ($energyLevel <= 30) {
            $currentMood = 'tired';
        } elseif ($stressLevel >= 70) {
            $currentMood = 'stressed';
        } elseif ($happinessLevel <= 30) {
            $currentMood = 'sad';
        } elseif (
            $hungerLevel >= 70 &&
            $happinessLevel >= 70 &&
            $energyLevel >= 70 &&
            $cleanlinessLevel >= 70 &&
            $stressLevel <= 30 &&
            $healthLevel >= 70
        ) {
            $currentMood = 'happy';
        } else {
            $currentMood = 'calmed';
        }

        return $currentMood;
    }

    public function sickCheck($status){
        $healthLevel = $status['health_level'];
        $is_sick = $status['is_sick'];
        if ($healthLevel <= 10) {
            $is_sick = 1;
        }
        else{
            $is_sick = 0;
        }
        return $is_sick;
    }

    public function severityCheck($status){
        $healthLevel = $status['health_level'];
        $stressLevel = $status['stress_level'];
        $hungerLevel = $status['hunger_level'];
        $happinessLevel = $status['happiness_level'];
        $energyLevel = $status['energy_level'];
        $cleanlinessLevel = $status['cleanliness_level'];
        $severityMultiplier = 1;
        if ($healthLevel <= 30 || $stressLevel >= 80 || $hungerLevel <= 20 || $happinessLevel <= 20 || $energyLevel <= 20 || $cleanlinessLevel <= 20){
            $severityMultiplier = 1.5;
        }
        else if ($healthLevel <= 20 || $stressLevel >= 60 || $hungerLevel <= 40 || $happinessLevel <= 40 || $energyLevel <= 40 || $cleanlinessLevel <= 40){
            $severityMultiplier = 2;
        }
        else if ($healthLevel <= 10 || $stressLevel >= 40 || $hungerLevel <= 60 || $happinessLevel <= 60 || $energyLevel <= 60 || $cleanlinessLevel <= 60){
            $severityMultiplier = 3;
        }

        return $severityMultiplier;
    }


    public function healthCheck($status){
        $stressLevel = $status['stress_level'];
        $hungerLevel = $status['hunger_level'];
        $happinessLevel = $status['happiness_level'];

        if ($stressLevel >= 0 || $hungerLevel <= 0 || $happinessLevel <= 0){
            $is_sick = 1;
        }
        else{
            $is_sick = 0;
        }
        return $is_sick;
    }


}

?>