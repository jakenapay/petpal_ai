<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use \Firebase\JWT\JWT;
use App\Models\UserModel;
use App\Models\PetModel;
use App\Models\PetStatusModel;


class Login extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
    }

    public function index()
    {
        // Retrieve JSON payload and decode into associative array
        $json = $this->request->getJSON(true);

        // Retrieve, trim and sanitize input
        $username = isset($json['username']) ? htmlspecialchars(trim($json['username']), ENT_QUOTES, 'UTF-8') : '';
        $password = $json['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Username and password are required']);
        }

        $userModel = new \App\Models\UserModel();

        // Check if user exists
        $user = $userModel->where('username', $username)->first();
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid username or password']);
        }

        // Check account status
        if (in_array($user['status'], ['inactive', 'suspended'])) {

            // get user's email by username
            $userEmail = $userModel->where('username', $username)->first()['email'];

            return $this->response
            ->setStatusCode(403)
            ->setJSON([
                'error' => 'Your account is ' . $user['status'] . '. Please contact support.',
                'email' => $userEmail,
            ]);
        }

        // Insert last login time 
        $userModel->update($user['user_id'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        // Generate JWT token
        $token = $this->generateJWT($user);

        // Check if user has a pet
        $petModel = new \App\Models\PetModel();
        $petModel->where('user_id', $user['user_id']);
        $count = $petModel->countAllResults();

        // Apply decay to pet statuses if user has pets
        if ($count > 0) {
            $applyDecay = $this->applyDecay($user);
        }
        
        return $this->response
            ->setStatusCode(200)
            ->setJSON([
            'success' => 'Login successful',
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'status' => $user['status'],
            'last_login' => $user['last_login'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
            'profile_img' => $user['profile_image'],
            'mbti' => $user['mbti'],
            'coins' => $user['coins'],
            'diamonds' => $user['diamonds'],
            'user_grade' => $user['user_grade'],
            'pet_count' => $count,
            'token'   => $token,
            'decay' => $applyDecay ?? false
            ]);
    }

    private function generateJWT($user)
    {
        $key = getenv('JWT_SECRET');
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $user['user_id'],
            'role' => $user['role'],
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    private function applyDecay($userData){

        //get the current time
        $now = new \DateTime();
        //get the user's logout_time.
        $logoutTime = new \DateTime($userData['logout_time']);
        //calculate the difference between the current time and the logout time.
        $interval = $now->diff($logoutTime);
        //get the total seconds of the interval.
        $secondsDiff = $now->getTimestamp() - $logoutTime->getTimestamp();

        //calculate the decay rate based on the total seconds.
        $decayRate = 1.67 / 5; // e.g., ~0.33 per minute\;
        $decayAmount = floor(($secondsDiff / 60) * $decayRate);
        //get the petIds of the user.
        $petModel = new PetModel();
        $petIds = $petModel->where('user_id', $userData['user_id'])->findColumn('pet_id');
        if (!$petIds) {
            return [
                'message' => 'No pets found for user.',
            ]; // No pets to apply decay to
        }
        //recursivley get the pet status of each pet.
        $petStatusModel = new PetStatusModel();
        $petStatuses = [];
        foreach ($petIds as $petId) {
            $statusData = $petStatusModel->where('pet_id', $petId)->first();
            if ($statusData) {
                $petStatuses[$petId] = $statusData;
            }
        }
        if (empty($petStatuses)) {
            return false; // No pet statuses found
        }

        if ($decayAmount <= 0) {
            return [
                'message' => 'No decay to apply.',
            ]; // No decay to apply
        }

        //recursivley apply decay to each pet.
        foreach ($petStatuses as $petId => $statusData) {
            //first check if the pet is sick. if no, just apply the basedecay.
            $is_sick = $this->sickCheck($statusData);

            $petStatusModel->update($statusData['status_id'], [
                'is_sick' => $is_sick,
            ]);

            if($is_sick === 1 ){
                $sicknessMultiplier = $this->severityCheck($statusData);
                $decay = $this->multipliedDecay($statusData, $decayAmount, $sicknessMultiplier);
            }else{
                $decay = $this->baseDecay($statusData, $decayAmount);
            }
            log_message('debug', "Decay result for pet $petId: " . json_encode($decay));

            $updateSuccess = $petStatusModel->update($statusData['status_id'], [
                'hunger_level' => $decay['hunger_level'],
                'happiness_level' => $decay['happiness_level'],
                'energy_level' => $decay['energy_level'],
                'cleanliness_level' => $decay['cleanliness_level'],
                'stress_level' => $decay['stress_level'],
                'health_level' => $decay['health_level'],
                'current_mood' => $this->moodChange($decay),
                // 'is_sick' => $is_sick,
                // 'sickness_severity' => $newSicknessSeverity,
                'last_happiness_update' => $now->format('Y-m-d H:i:s'),
                'last_energy_update' => $now->format('Y-m-d H:i:s'),
                'last_cleanliness_update' => $now->format('Y-m-d H:i:s'),
                'last_health_update' => $now->format('Y-m-d H:i:s'),
                'last_hunger_update' => $now->format('Y-m-d H:i:s'),
                'last_status_calculation' => $now->format('Y-m-d H:i:s')
            ]);
            log_message('debug', "Update success for pet $petId: " . ($updateSuccess ? 'true' : 'false'));
        }
        
        //get the new pet status
        $newPetStatuses = [];
        foreach ($petStatuses as $petId => $statusData) {
            $petStatusModel->resetQuery(); // Important!
            $newPetStatuses[$petId] = $petStatusModel->where('pet_id', $petId)->first();
        }

        return [
            'message' => 'Decay applied successfully.',
            'date_now' => $now->format('Y-m-d H:i:s'),
            'logout_time' => $logoutTime->format('Y-m-d H:i:s'),
            'decay_amount' => $decayAmount,
            'old_status' => $petStatuses,
            'new_status' => $newPetStatuses
        ];
        
    }


    private function baseDecay($statusData, $decayAmount){
        $newHunger = max(0, $statusData['hunger_level'] - $decayAmount);
        $newHappiness = max(0, $statusData['happiness_level'] - $decayAmount);
        $newEnergy = min(100, max(0, $statusData['energy_level'] + $decayAmount));
        $newCleanliness = max(0, $statusData['cleanliness_level'] - $decayAmount);
        $newStress = min(100, max(0, $statusData['stress_level'] + $decayAmount));
        $newHealth = $statusData['health_level'];
        if($statusData['stress_level'] >=50 || $statusData['hunger_level'] <= 20 || $statusData['happiness_level'] <= 20){
            $newHealth = min(100, max(0, $statusData['health_level'] - $decayAmount));
        }

        return [
            'hunger_level' => $newHunger,
            'happiness_level' => $newHappiness,
            'energy_level' => $newEnergy,
            'cleanliness_level' => $newCleanliness,
            'health_level' => $newHealth,
            'stress_level' => $newStress,
        ];
    }


    private function multipliedDecay($statusData, $decayAmount, $multiplier){
        $newHunger = max(0, $statusData['hunger_level'] - $decayAmount * $multiplier);
        $newHappiness = max(0, $statusData['happiness_level'] - $decayAmount * $multiplier);
        $newEnergy = min(100, max(0, $statusData['energy_level'] - $decayAmount * $multiplier));
        $newCleanliness = max(0, $statusData['cleanliness_level'] - $decayAmount * $multiplier);
        $newStress = max(0, $statusData['stress_level'] + $decayAmount * $multiplier);
        $newHealth = $statusData['health_level'];
        if($statusData['stress_level'] >=50 || $statusData['hunger_level'] <= 20 || $statusData['happiness_level'] <= 20){
            $newHealth = min(100, max(0, $statusData['health_level'] - $decayAmount * $multiplier));
        }

        return [
            'hunger_level' => $newHunger,
            'happiness_level' => $newHappiness,
            'energy_level' => $newEnergy,
            'cleanliness_level' => $newCleanliness,
            'health_level' => $newHealth,
            'stress_level' => $newStress,
        ];
    }

    private function sickCheck($statusData){
        $healthLevel = $statusData['health_level'];
        $is_sick = $statusData['is_sick'];
        if ($healthLevel <= 10) {
            $is_sick = 1;
        }
        else{
            $is_sick = 0;
        }
        return $is_sick;
    }

    private function severityCheck($statusData){
        $healthLevel = $statusData['health_level'];
        $stressLevel = $statusData['stress_level'];
        $hungerLevel = $statusData['hunger_level'];
        $happinessLevel = $statusData['happiness_level'];
        $energyLevel = $statusData['energy_level'];
        $cleanlinessLevel = $statusData['cleanliness_level'];
        $severityMultiplier = 1;

        if ($healthLevel <= 10 || $stressLevel >= 90 || $hungerLevel <= 10 || $happinessLevel <= 10 || $energyLevel <= 10 || $cleanlinessLevel <= 10) {
            $severityMultiplier = 2; // 🔥 Most severe
        } 
        else if ($healthLevel <= 20 || $stressLevel >= 70 || $hungerLevel <= 30 || $happinessLevel <= 30 || $energyLevel <= 30 || $cleanlinessLevel <= 30) {
            $severityMultiplier = 1.75; // ⚠️ Moderate
        }
        else if ($healthLevel <= 30 || $stressLevel >= 50 || $hungerLevel <= 50 || $happinessLevel <= 50 || $energyLevel <= 50 || $cleanlinessLevel <= 50) {
            $severityMultiplier = 1.5; // ⚠️ Mild
        }

        return $severityMultiplier;
    }

    public function moodChange($status)
    {
        $hungerLevel = $status['hunger_level'];
        $happinessLevel = $status['happiness_level'];
        $energyLevel = $status['energy_level'];
        $cleanlinessLevel = $status['cleanliness_level'];
        $stressLevel = $status['stress_level'];
        $healthLevel = $status['health_level'];

        $score = 0;

        // Negative mood penalties
        if ($healthLevel <= 30) $score -= 3;
        if ($hungerLevel <= 30) $score -= 2;
        if ($cleanlinessLevel <= 30) $score -= 2;
        if ($energyLevel <= 30) $score -= 2;
        if ($stressLevel >= 70) $score -= 2;
        if ($happinessLevel <= 30) $score -= 1;

        // Positive mood bonuses
        if ($healthLevel >= 70) $score += 2;
        if ($hungerLevel >= 70) $score += 1;
        if ($cleanlinessLevel >= 70) $score += 1;
        if ($energyLevel >= 70) $score += 1;
        if ($stressLevel <= 30) $score += 1;
        if ($happinessLevel >= 70) $score += 2;

        // Mood logic based on score ranges
        if ($score >= 6) {
            return 'happy';
        } elseif ($score >= 3) {
            return 'calmed';
        } elseif ($score >= 0) {
            return 'tired';
        } elseif ($score >= -3) {
            return 'stressed';
        } elseif ($score >= -6) {
            return 'sad';
        } else {
            return 'ill';
        }
    }
}
