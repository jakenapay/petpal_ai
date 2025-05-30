<?php

namespace App\Models;

use CodeIgniter\Model;

class GachaPullModel extends Model
{
    protected $table            = 'gacha_pulls';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        // 'id',
        'player_id',
        'pool_id',
        'item_received',
        'spent',
        'diamonds_spent',
        'pull_timestamp',
        'pity_count',
        'is_pity',
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

    public function getPityCount($player_id, $pool_id)
    {
        return $this->where('player_id', $player_id)
                    ->where('pool_id', $pool_id)
                    ->orderBy('id', 'DESC') // or 'id' if autoincrement
                    ->limit(1)
                    ->get()
                    ->getRow('pity_count') ?? 0; // Return 0 if no pulls yet
    }


    public function playerPull($data)
    {
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Error inserting player pull: ' . $e->getMessage());
            return false;
        }
    }



    
}
