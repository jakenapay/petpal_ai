<?php

namespace App\Models;

use CodeIgniter\Model;

class AdoptionModel extends Model
{
    protected $table            = 'adoptionmodel';
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

    //Functions

    public function getCatTexture($breed_id){
        $db = \Config\Database::connect();
        $builder = $db->table('cat_texture');
        $builder->where('breed_id', $breed_id);
        $builder->where('life_stage_id', 1);
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();

        return $query->getResult(); 
    }

    public function getDogTexture($breed_id){
        $db = \Config\Database::connect();
        $builder = $db->table('dog_texture');
        $builder->where('breed_id', $breed_id);
        $builder->where('life_stage_id', 1);
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();

        return $query->getResult(); 
    }

}
