<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AchievementModel;

class GetPetAchievements extends BaseController
{
    public function index($pet_id)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Validate pet_id
        $pet_id = (int) $pet_id;
        if (!$pet_id) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Fetch pet achievements
        $achievementModel = new AchievementModel();
        $achievements = $achievementModel->getAchievementsByPetId($pet_id);

        if (!$achievements) {
            return $this->response->setJSON(['error' => 'No achievements found for this pet'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // ✅ Always return something if successful
        return $this->response->setJSON(['achievements' => $achievements])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
