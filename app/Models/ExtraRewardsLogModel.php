<?php

namespace App\Models;

use CodeIgniter\Model;

class ExtraRewardsLogModel extends Model
{
    protected $table            = 'quest_extra_rewards_log';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'reward_id',
        'reward_category',
        'is_claimed',
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

    // Functions
    public function getExtraRewardsLogDaily($userId, $rewardId)
    {
        log_message('debug', "getExtraRewardsLogDaily($userId, $rewardId)");
        date_default_timezone_set('Asia/Manila');
        // Get today's date in Asia/Manila timezone
        $today = date('Y-m-d');

        // Query to get today's extra rewards log for the user
        $this->where('user_id', $userId);
        if ($rewardId !== "ALL") {
            $this->where('reward_id', $rewardId);
        }
        $this->where('DATE(created_at)', $today);

        return $this->findAll();
    }
    public function getExtraRewardsLogWeekly($userId, $rewardId)
    {
        log_message('debug', "getExtraRewardsLogWeekly($userId, $rewardId)");
        date_default_timezone_set('Asia/Manila');
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        log_message('debug', "startOfWeek: " . $startOfWeek . " endOfWeek: " . $endOfWeek);
        // Query to get today's extra rewards log for the user
        $this->where('user_id', $userId);
        if ($rewardId !== "ALL") {
            $this->where('reward_id', $rewardId);
        }
        $this->where('DATE(created_at) >=', $startOfWeek);
        $this->where('DATE(created_at) <=', $endOfWeek);

        return $this->findAll();
    }
    public function insertExtraRewardLog($data)
    {
        date_default_timezone_set('Asia/Manila');
        // Insert a new extra reward log entry
        return $this->insert($data);
    }
}
