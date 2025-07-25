<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\GachaPoolModel;
class PullOptionsModel extends Model
{
    protected $table            = 'gacha_pull_options';
    protected $primaryKey       = 'id';
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

    public function getPullOptions($pool_id = null){
        $builder = $this->db->table($this->table);
        $builder->where('pool_id', $pool_id);
        return $builder->get()->getResultArray();
    }
    public function getSpecificPullOption($id = null){
        $builder = $this->db->table($this->table);
        $builder->where('id', $id);
        return $builder->get()->getRowArray();
    }
}
