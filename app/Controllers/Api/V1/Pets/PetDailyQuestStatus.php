<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DailyQuestModel;

class PetDailyQuestStatus extends BaseController
{
    public function index($pet_id)
    {
        // Authorize
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

        // Fetch daily quest status
        $dailyQuestModel = new DailyQuestModel();
        $dailyQuests = $dailyQuestModel->getDailyQuests($pet_id);

        if (!$dailyQuests) {
            return $this->response->setJSON(['error' => 'No daily quests found for this pet'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // ✅ Return the quests
        return $this->response->setJSON(['quests' => $dailyQuests])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
