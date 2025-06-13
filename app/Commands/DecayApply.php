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

                $petStatusModel->update($status['status_id'], [
                    'hunger_level' => $newHunger,
                    'last_hunger_update' => $now->format('Y-m-d H:i:s'),
                    'last_status_calculation' => $now->format('Y-m-d H:i:s')
                ]);

                CLI::write("Pet ID {$pet['pet_id']} decayed: -{$decayAmount} hunger", 'green');
            }
        }
    }
}

?>