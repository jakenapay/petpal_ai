<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'profile_image',
        'last_login',
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
