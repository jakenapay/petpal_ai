<?php

namespace App\Models;

use CodeIgniter\Model;

class WeeklyQuestModel extends Model
{
    protected $table            = 'weekly_quests';
    protected $primaryKey       = 'quest_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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

    //FUNCTIONS
    public function getWeeklyQuests()
    {
        return $this->where('is_active', 1)
            ->findAll();
    }

    public function getWeeklyQuestById($questId)
    {
        return $this->where('quest_id', $questId)
            ->first();
    }


    public function getWeeklyQuestStatus($userId)
    {
        $dailyQuestsLogsModel = new DailyQuestsLogsModel();
        $result = $dailyQuestsLogsModel->getDailyQuestLogs($userId);
        if (!$result) {
            return null; // No logs found for the user
        }
        return $result;
        
    }
}
