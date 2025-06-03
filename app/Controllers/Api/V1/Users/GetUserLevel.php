<?php

namespace App\Controllers\Api\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\UserLevelModel;

class GetUserLevel extends BaseController
{
    public function index()
    {
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43; 
        // get the experience of the user
        $userModel = new UserModel();
        $experience = $userModel->getUserExperience($userId);
        if ($experience === false) {
            return $this->response->setJSON(['error' => 'User not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //get the needed user level to achieve the next level
        $userLevelModel = new UserLevelModel();
        $userLevel = $experience['user_level'] + 1; // Get the next level
        $requiredExperience = $userLevelModel->getUserRequiredExperience($userLevel);
        if ($requiredExperience === null) {
            return $this->response->setJSON(['error' => 'Required experience for next level not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'message' => 'User level retrieved successfully',
            'data' => [
                'experience' => $experience['experience'],
                'user_level' => $experience['user_level'],
                'required_experience' => $requiredExperience['experience_required']

            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK); 
    }
}
