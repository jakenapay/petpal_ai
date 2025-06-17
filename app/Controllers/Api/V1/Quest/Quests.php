<?php

namespace App\Controllers\Api\V1\Quest;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DailyQuestModel;
use App\Models\DailyQuestsLogsModel;
use App\Models\UserModel;
use App\Models\UserLevelModel;
use App\Models\DailiesExtraRewardsModel;
use App\Models\ExtraRewardsLogModel;
use App\Models\WeeklyExtraRewardsModel;
use App\Models\ItemModel;
use App\Models\WeeklyQuestModel;
use App\Models\WeeklyQuestsLogsModel;
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
        $existingLogs = $dailyQuestLogsModel->getDailyQuestLogs($userId);
        if ($existingLogs) {
            return $this->response->setJSON([
                'message' => 'Daily quests status for user was successfully retrieved.',
                'quests' => [
                    'daily_quests' => $dailyQuests,
                    'daily_quests_status' => $existingLogs
                    ]
            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
        }else{
            $existingLogs = [];
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
                'quests' => $dailyQuests,
                'quests_status' => $existingLogs
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

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

            //update the user coins, experience and user grade
            $updateCoinsResult = $userModel->updateCoins($userId, $newCoins);
            if (!$updateCoinsResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update user coins'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
            //here, im not sure yet kasi di ko pa netetest if max na yung level ng user
            $updateExperienceResult = $userModel->handleUserLevelUp($userId, $newExperience);
            if (!$updateExperienceResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to handle user level up'])
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

    public function dailyQuestExtraRewards(){
        // $userId = 43;

        // auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        //count the number of finished task of the user this day
        $dailyQuestLogsModel = new DailyQuestsLogsModel();
        $completedTaskCount = $dailyQuestLogsModel->getCompletedQuestCountThisWeek($userId) ?? 0;
        $dailiesExtraRewardsModel = new DailiesExtraRewardsModel();
        $extraRewards = $dailiesExtraRewardsModel->getExtraRewards();

        //post to the logs of extra rewards for the user
        $extraRewardsLogModel = new ExtraRewardsLogModel();
        if ($completedTaskCount === 0){
            //add logs so that we will just update it later.
            $data = [
                'user_id' => $userId,
                'reward_id' => 0, // No reward claimed yet
                'reward_category' => 'N/A',
                'is_claimed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $extraRewardsLogModel->insert($data);
        }

        $extraRewardsLog = $extraRewardsLogModel->getExtraRewardsLog($userId, $rewardId = "ALL");


        //loop through the extra rewards and match it with the extra rewards log so that i can show 
        //if the user already claimed the reward or not.
        foreach ($extraRewards as &$reward) {
            $reward['is_claimed'] = false; // Default to not claimed
            foreach ($extraRewardsLog as $log) {
                if ($log['reward_id'] == $reward['reward_id']) {
                    $reward['is_claimed'] = $log['is_claimed'] == 1 ? true : false;
                    break;
                }
            }
        }
        
        return $this->response->setJSON([
            'message' => 'Completed tasks count for the user this day has been retrieved successfully',
            'data' => [
                'completed_tasks_count' => $completedTaskCount,
                'extra_rewards_task' => $extraRewards
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function claimExtraReward(){
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $data = $this->request->getJSON(true);
        if (!$data || !isset($data['reward_id'])) {
            return $this->response->setJSON(['error' => 'Reward ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        $rewardId = (int) $data['reward_id'];
        if (!$rewardId) {
            return $this->response->setJSON(['error' => 'Invalid Reward ID'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        //get the details of the reward
        $dailiesExtraRewardsModel = new DailiesExtraRewardsModel();
        $weeklyExtraRewardsModel = new WeeklyExtraRewardsModel();
        $rewardDetails = $dailiesExtraRewardsModel->find($rewardId);
        $rewardCategory = 'daily';

        if (!$rewardDetails) {
            $rewardDetails = $weeklyExtraRewardsModel->find($rewardId);
            $rewardCategory = 'weekly';
        }
        //check if the user already claimed the reward
        $extraRewardsLogModel = new ExtraRewardsLogModel();
        $existingLog = $extraRewardsLogModel->getExtraRewardsLog($userId, $rewardId);
        if ($existingLog) {
            return $this->response->setJSON(['info' => 'Reward already claimed'])
                ->setStatusCode(ResponseInterface::HTTP_OK);
        }

        //count the number of finished task of the user this day
        $dailyQuestLogsModel = new DailyQuestsLogsModel();
        $completedTaskCount = $dailyQuestLogsModel->getCompletedQuestCountThisWeek($userId) ?? 0;
        $dailiesExtraRewardsModel = new DailiesExtraRewardsModel();
        $extraRewards = $dailiesExtraRewardsModel->getExtraRewards();
        // Check if the user has completed enough tasks to claim the reward
        if ($completedTaskCount < $rewardDetails['requirement_value']) {
            return $this->response->setJSON(['error' => 'You need to finish ' . $rewardDetails['requirement_value'] . ' tasks to claim this reward'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }


        //if not claimed, insert the log
        $db = \Config\Database::connect();
        $db->transStart();
        $data = [
            'user_id' => $userId,
            'reward_id' => $rewardId,
            'reward_category' => $rewardCategory,
            'is_claimed' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $result = $extraRewardsLogModel->insert($data);
        if (!$result) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to claim extra reward'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }


        $itemModel = new ItemModel();
        //get the "reward value" and make it as the item id
        $itemId = $rewardDetails['reward_value'];

        //check if the item exists
        $itemExists = $itemModel->find($itemId);
        if (!$itemExists) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Item not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        //get the quantity to be add in the user inventory
        $quantity = $rewardDetails['reward_quantity'] ?? 1; 
        //add the item to the user's inventory
        //not required yet 
        
        $itemDetails = [
            'item_id' => $itemId,
            'item_name' => $itemExists['item_name'],
        ];

        //complete the transaction
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->response->setJSON([
            'message' => 'Extra reward has been claimed successfully',
            'item_details' => $itemDetails,
            'quantity' => $quantity,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
        

    }


    public function weeklyQuestStatus(){
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $weeklyQuestModel = new WeeklyQuestModel();
        $weeklyQuests = $weeklyQuestModel->getWeeklyQuests();
        if (!$weeklyQuests) {
            return $this->response->setJSON(['error' => 'No weekly quests found. Please check your database.'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Check if the user already has weekly quests logged
        $weeklyQuestsLogsModel = new WeeklyQuestsLogsModel();
        $existingLogs = $weeklyQuestsLogsModel->getWeeklyQuestLogs($userId);
        if ($existingLogs) {
            return $this->response->setJSON([
                'message' => 'Weekly quests status for user was successfully retrieved.',
                'quests' => ['
                    weekly_quests' => $weeklyQuests, 
                    'weekly_quests_status' => $existingLogs]
            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
        }else{
            $existingLogs = [];
        }
        //post to the weekly quest logs table the daily quests
        foreach ($weeklyQuests as $quest) {
            $weeklyQuestsLogsModel->insertWeeklyQuestLog([
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

        if (!$weeklyQuestsLogsModel) {
            return $this->response->setJSON(['error' => 'Failed to log weekly quests'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return the daily quests
        return $this->response->setJSON([
            'message' => 'Weekly quests status for user was successfully retrieved.',
            'data' => [
                'quests' => $weeklyQuests,
                'weekly_quests_status' => $existingLogs
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function updateWeeklyQuest(){
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
        $weeklyQuestModel = new WeeklyQuestModel();
        $questDetails = $weeklyQuestModel->getWeeklyQuestById($questId);
        if (!$questDetails) {
            return $this->response->setJSON(['error' => 'Quest not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //check if the user had already completed the quest today.
        $weeklyQuestsLogsModel = new WeeklyQuestsLogsModel();
        $existingLog = $weeklyQuestsLogsModel->getWeeklyQuestLogs($userId, $questId);
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
        // Update the weekly quest log with the new current count
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

            //update the user coins, experience and user grade
            $updateCoinsResult = $userModel->updateCoins($userId, $newCoins);
            if (!$updateCoinsResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update user coins'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
            //here, im not sure yet kasi di ko pa netetest if max na yung level ng user
            $updateExperienceResult = $userModel->handleUserLevelUp($userId, $newExperience);
            if (!$updateExperienceResult) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to handle user level up'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        $result = $weeklyQuestsLogsModel->updateWeeklyQuestLog($questId, $data);
        if (!$result) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update weekly quest. '])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Weekly quest "' . $questDetails['quest_name'] . '" has been ' . ($updateCount >= $questDetails['target_count'] ? 'completed' : 'updated') . ' successfully'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function weeklyQuestExtraRewards(){
        // $userId = 43;

        // auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        //count the number of finished task of the user this day
        $weeklyQuestsLogsModel= new WeeklyQuestsLogsModel();
        $completedTaskCount = $weeklyQuestsLogsModel->getCompletedQuestCountToday($userId) ?? 0;
        $weeklyExtraRewardsModel = new WeeklyExtraRewardsModel();
        $extraRewards = $weeklyExtraRewardsModel->getExtraRewards();

        //post to the logs of extra rewards for the user
        $extraRewardsLogModel = new ExtraRewardsLogModel();
        if ($completedTaskCount === 0){
            //add logs so that we will just update it later.
            $data = [
                'user_id' => $userId,
                'reward_id' => 0, // No reward claimed yet
                'reward_category' => 'N/A',
                'is_claimed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $extraRewardsLogModel->insert($data);
        }

        $extraRewardsLog = $extraRewardsLogModel->getExtraRewardsLog($userId, $rewardId = "ALL");


        //loop through the extra rewards and match it with the extra rewards log so that i can show 
        //if the user already claimed the reward or not.
        foreach ($extraRewards as &$reward) {
            $reward['is_claimed'] = false; // Default to not claimed
            foreach ($extraRewardsLog as $log) {
                if ($log['reward_id'] == $reward['reward_id']) {
                    $reward['is_claimed'] = $log['is_claimed'] == 1 ? true : false;
                    break;
                }
            }
        }
        
        return $this->response->setJSON([
            'message' => 'Completed tasks count for the user this week has been retrieved successfully',
            'data' => [
                'completed_tasks_count' => $completedTaskCount,
                'extra_rewards_task' => $extraRewards,
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);


    }

}
