<?php

namespace App\Models;

use CodeIgniter\Model;

class GachaPoolModel extends Model
{
    protected $table = 'gacha_pools';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description',
        'image_url',
        'start_date',
        'end_date',
        'is_active',
        'type_id',
        'pity_limit',
        'pity_item_id',
        'pity_reset_on_hit'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    // public function getGachaPools($pool_id = null) {


    //     $builder = $this->db->table($this->table);
    //     $builder->where('is_active', 1);
    //     if ($pool_id) {
    //         $builder->where('id', $pool_id);
    //         return $builder->get()->getRowArray();
    //     } else {
    //         return $builder->get()->getResultArray();
    //     }
    // }

    // SELECT * FROM `gacha_pull_options`
    // WHERE pool_id = '550e8400-e29b-41d4-a716-446655440001';

    public function getGachaPools($pool_id = null)
    {
        $builder = $this->db->table($this->table);
        $builder->where("{$this->table}.is_active", 1);
        if ($pool_id) {
            $builder->where("{$this->table}.id", $pool_id);
        }
        $pools = $builder->get()->getResultArray();

        foreach ($pools as &$pool) {
            $options = $this->db->table('gacha_pull_options')
                ->where('pool_id', $pool['id'])
                ->get()->getResultArray();
            $pool['pull_options'] = $options;
        }

        return $pool_id ? $pools[0] : $pools;
    }




}
