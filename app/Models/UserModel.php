<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserLevelModel;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'mbti',
        'profile_image',
        'last_login',
        'logout_time',
        'status',
        'role',
        'verification_code',
        'verification_expiration_date',
        'number_of_pets',
        'coins',
        'diamonds',
        'experience',
        'user_grade',
        
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUserBalance($user_id){
        $user = $this->find($user_id);
        //get only the diamonds and coins
        $result = [
            'diamonds' => $user['diamonds'],
            'coins' => $user['coins']
        ];
        return $result;
    }
    public function updateCoins($user_id, $amount){
        $update =$this->update($user_id, ['coins' => $amount]);
        return $update;
    }
    public function updateDiamonds($user_id, $amount){
        $update = $this->update($user_id, ['diamonds' => $amount]);
        return $update;
    }
    public function updateUserExperience($user_id, $experience, $user_grade)
    {
        return $this->update($user_id, [
            'experience' => $experience,
            'user_grade' => $user_grade
        ]);
    }

    public function handleUserLevelUp($user_id, $newExperience)
    {
        // Get the current user's experience and level
        $user = $this->find($user_id);
        $currentExperience = $user['experience'] ?? 0;
        $userLevel = $user['user_grade'] ?? 0;

        // Get the total number of levels from the table
        $userLevelModel = new UserLevelModel();
        $totalLevels = $userLevelModel->countAllResults();

        // If user is already at max level, just return true without changing anything
        if ($userLevel >= $totalLevels) {
            return true;
        }

        // Get required XP for next level
        $userNextLevel = $userLevelModel->getUserRequiredExperience($userLevel + 1);
        if (!$userNextLevel) {
            log_message('error', 'User level not found for user ID: ' . $user_id);
            return false;
        }

        // Check if user qualifies for level up
        if ($newExperience >= $userNextLevel['experience_required']) {
            $userLevel += 1;
        }

        // Update user experience and level
        return $this->update($user_id, [
            'experience' => $newExperience,
            'user_grade' => $userLevel
        ]);
    }


    public function getUserExperience($user_id)
    {
        $user = $this->find($user_id);
        if ($user) {
            return [
                'experience' => $user['experience'] ?? 0,
                'user_level' => $user['user_grade'] ?? 0
            ];
        }
        return false; 
    }
    public function getUserById($userId)
    {
        // Retrieve user details by user ID
        return $this->where('user_id', $userId)->first();
    }

}
