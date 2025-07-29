<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\WeeklyQuestsLogsModel;
use App\Models\WeeklyQuestModel;

class WeeklyQuestsLogsModel extends Model
{
    protected $table            = 'weekly_quests_logs';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'quest_id',
        'target_count',
        'current_count',
        'is_completed',
        'reward_claimed',
        'date_assigned',
        'completed_at',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

        //Functions
    public function insertWeeklyQuestLog($data)
    {
        // Insert a new daily quest log entry
        return $this->insert($data);
    }
    public function getWeeklyQuestsByUserId($userId)
    {
        $weeklyQuestsLogsModel = new WeeklyQuestsLogsModel();
        $result = $weeklyQuestsLogsModel->getWeeklyQuestLogs($userId);
        if (!$result) {
            return null; // No logs found for the user
        }
        
        $questIds = array_column($result, 'quest_id');
        return $this->whereIn('quest_id', $questIds)
            ->findAll();
    }
    // public function getWeeklyQuestLogs($userId, $questId = null){
    //     //get the daily quest log for the user today
        
    //     $today = date('Y-m-d');
    //     return $this->where('user_id', $userId)
    //                 ->where('DATE(created_at)', $today)
    //                 ->when($questId, function($query) use ($questId) {
    //                     return $query->where('quest_id', $questId);
    //                 })
    //                 ->findAll();
    // }

    public function getWeeklyQuestLogs($userId, $questId = null) {
        // Get the start (Monday) and end (Sunday) of the current week
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        return $this->where('user_id', $userId)
                    ->where('DATE(created_at) >=', $startOfWeek)
                    ->where('DATE(created_at) <=', $endOfWeek)
                    ->when($questId, function($query) use ($questId) {
                        return $query->where('quest_id', $questId);
                    })
                    ->findAll();
    }

    public function getCompletedQuestCountThisWeek($userId){
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        return $this->where('user_id', $userId)
                    ->where('is_completed', 1)
                    ->where('DATE(created_at) >=', $startOfWeek)
                    ->where('DATE(created_at) <=', $endOfWeek)
                    ->countAllResults();
    }


    public function updateWeeklyQuestLog($userId, $quest_id, $data)
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        return $this->where('quest_id', $quest_id)
                    ->where('user_id', $userId)
                    ->where('DATE(created_at) >=', $startOfWeek)
                    ->where('DATE(created_at) <=', $endOfWeek)
                    ->set($data)
                    ->update();

            }
}
