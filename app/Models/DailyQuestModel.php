<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\DailyQuestsLogsModel;

class DailyQuestModel extends Model
{
    protected $table            = 'daily_quests';
    protected $primaryKey       = 'quest_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quest_id',
        'quest_name',
        'quest_type',
        'description',
        'target_count',
        'reward_coins',
        'reward_experience',
        'is_mandatory',
        'is_active',
        'created_at'
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

    //FUNCTIONS
    public function getDailyQuests()
    {
        return $this->where('is_active', 1)
            ->findAll();
    }

    public function getDailyQuestById($questId)
    {
        return $this->where('quest_id', $questId)
            ->first();
    }


    public function getDailyQuestStatus($userId)
    {
        $dailyQuestsLogsModel = new DailyQuestsLogsModel();
        $result = $dailyQuestsLogsModel->getDailyQuestLogs($userId);
        if (!$result) {
            return null; // No logs found for the user
        }
        return $result;
        
    }
}
