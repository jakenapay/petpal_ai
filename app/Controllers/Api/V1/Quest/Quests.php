<?php

namespace App\Controllers\Api\V1\Quest;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DailyQuestModel;
use App\Models\DailyQuestsLogsModel;
use App\Models\UserModel;
use App\Models\UserLevelModel;
class Quests extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {
        //pet_daily_actions = logs
        // daily_quests = daily quests
        // daily_care_quest_templates
    }
    public function dailyQuestStatus(){
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $dailyQuestModel = new DailyQuestModel();
        $dailyQuests = $dailyQuestModel->getDailyQuests();
        if (!$dailyQuests) {
            return $this->response->setJSON(['error' => 'No daily quests found. Please check your database.'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Check if the user already has daily quests logged
        $dailyQuestLogsModel = new DailyQuestsLogsModel();
        $existingLogs = $dailyQuestLogsModel->getDailyQuestsByUserId($userId);
        if ($existingLogs) {
            return $this->response->setJSON([
                'message' => 'Daily quests status for user was successfully retrieved.',
                'quests' => ['daily_quests' => $dailyQuests, 'daily_quests_status' => $existingLogs]
            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
        }
        //post to the dailyqusetlogs table the daily quests
        $dailyQuestLogsModel = new DailyQuestsLogsModel();
        foreach ($dailyQuests as $quest) {
            $dailyQuestLogsModel->insertDailyQuestLog([
                'user_id' => $userId,
                'quest_id' => $quest['quest_id'],
                'is_completed' => 0,
                'reward_claimed' => 0,
                'target_count' => $quest['target_count'],
                'current_count' => 0,
                'date_assigned' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        if (!$dailyQuestLogsModel) {
            return $this->response->setJSON(['error' => 'Failed to log daily quests'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        // Return the daily quests
        return $this->response->setJSON([
            'message' => 'Daily quests retrieved successfully',
            'data' => [
                'quests' => $dailyQuests
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    // public function dailyQuestStatus(){
    //     //auth check
    //     $userId = authorizationCheck($this->request);
    //     // if (!$userId) {
    //     //     return $this->response->setJSON(['error' => 'Unauthorized'])
    //     //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    //     // }
    //     $userId = 43;
    //     $dailyQuestModel = new DailyQuestModel();
    //     $dailyQuestStatus = $dailyQuestModel->getDailyQuestStatus( $userId);
    //     if (!$dailyQuestStatus) {

    //     }
    //     // Return the daily quest status
    //     return $this->response->setJSON([
    //         'message' => 'Daily quest status retrieved successfully',
    //         'data' => [
    //             'status' => $dailyQuestStatus
    //         ]
    //     ])->setStatusCode(ResponseInterface::HTTP_OK);
    // }

    public function updateDailyQuest(){
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;
        $data = $this->request->getJSON(true);
        if (!$data || !isset($data['quest_id'])) {
            return $this->response->setJSON(['error' => 'Quest ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        $questId = (int) $data['quest_id'];
        if (!$questId) {
            return $this->response->setJSON(['error' => 'Invalid Quest ID'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        //get the details of the quest
        $dailyQuestModel = new DailyQuestModel();
        $questDetails = $dailyQuestModel->getDailyQuestById($questId);
        if (!$questDetails) {
            return $this->response->setJSON(['error' => 'Quest not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //check if the user had already completed the quest today.
        $dailyQuestsLogsModel = new DailyQuestsLogsModel();
        $existingLog = $dailyQuestsLogsModel->getDailyQuestLogs($userId, $questId);
        if (!$existingLog) {
            return $this->response->setJSON(['error' => 'No log found for the user'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        //check if the quest is already completed
        if ($existingLog[0]['is_completed'] == 1) {
            return $this->response->setJSON(['info' => 'Quest already completed'])
                ->setStatusCode(ResponseInterface::HTTP_OK);
        }

        //if not completed, update the log by getting the current count and adding 1 to it.
        $currentCount = $existingLog[0]['current_count'];
        // Check if the current count is less than the target count
        if ($currentCount > $questDetails['target_count']) {
            return $this->response->setJSON(['error' => 'Current count exceeds target count'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        $db = \Config\Database::connect();
        $db->transStart();
        $updateCount = $currentCount + 1;
        $data = [
            'current_count' => $updateCount,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        // Update the daily quest log with the new current count
        if ($updateCount >= $questDetails['target_count']) {
            $data['is_completed'] = 1;
            $data['completed_at'] = date('Y-m-d H:i:s');
            $data['reward_claimed'] = 1; // Reset reward claimed status
            //update the user experience and coins
            $userModel = new UserModel();
            $user = $userModel->getUserById($userId);
            if (!$user) {
                return $this->response->setJSON(['error' => 'User not found'])
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            }
            $newExperience = $user['experience'] + $questDetails['reward_experience'];
            $newCoins = $user['coins'] + $questDetails['reward_coins'];

            //check if the user is eligible for a level up
            $userLevelModel = new UserLevelModel();
            $userNextLevel = $userLevelModel->getUserRequiredExperience($user['user_grade']+1);
            if (!$userNextLevel) {
                return $this->response->setJSON(['error' => 'User level not found'])
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            }
            $userGrade = $user['user_grade'];
            // Check if the new experience exceeds the required experience for the next level
            if ($newExperience >= $userNextLevel['experience_required']) {
                // Level up the user
                $userGrade = $user['user_grade'] += 1; // Increment user level
            }
            //update the user coins, experience and user grade
            $updateCoinsResult = $userModel->updateCoins($userId, $newCoins);
            if (!$updateCoinsResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update user coins'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }

            $updateExperienceResult = $userModel->updateUserExperience($userId, $newExperience, $userGrade);
            if (!$updateExperienceResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update user experience or grade'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        $result = $dailyQuestsLogsModel->updateDailyQuestLog($questId, $data);
        if (!$result) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update daily quest. '])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Daily quest "' . $questDetails['quest_name'] . '" has been ' . ($updateCount >= $questDetails['target_count'] ? 'completed' : 'updated') . ' successfully'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
