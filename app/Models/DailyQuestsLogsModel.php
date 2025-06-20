<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyQuestsLogsModel extends Model
{
    protected $table            = 'daily_quests_logs';
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
    public function insertDailyQuestLog($data)
    {
        // Insert a new daily quest log entry
        return $this->insert($data);
    }
    public function getDailyQuestsByUserId($userId)
    {
        $dailyQuestsLogsModel = new DailyQuestsLogsModel();
        $result = $dailyQuestsLogsModel->getDailyQuestLogs($userId);
        if (!$result) {
            return null; // No logs found for the user
        }
        
        $questIds = array_column($result, 'quest_id');
        return $this->whereIn('quest_id', $questIds)
            ->findAll();
    }
    public function getDailyQuestLogs($userId, $questId = null){
        //get the daily quest log for the user today
        
        $today = date('Y-m-d');
        return $this->where('user_id', $userId)
                    ->where('DATE(created_at)', $today)
                    ->when($questId, function($query) use ($questId) {
                        return $query->where('quest_id', $questId);
                    })
                    ->findAll();
    }
    public function getCompletedQuestCountThisDay($userId){
        // Count the completed daily quest logs for the user for tpday
        $today = date('Y-m-d');
        
        return $this->where('user_id', $userId)
                    ->where('is_completed', 1)
                    ->where('DATE(created_at)', $today)
                    ->countAllResults();
    }

    public function updateDailyQuestLog($quest_id, $data)
    {
        log_message('info', 'Updating daily quest log for quest_id: ' . $quest_id);
        log_message('info', 'Data to update: ' . json_encode($data));
        //get the today's date
        $today = date('Y-m-d');
        // Update a specific daily quest log entry
        return $this->where('quest_id', $quest_id)
                    ->where('DATE(created_at)', $today)
                    ->set($data)
                    ->update();
    }
}
