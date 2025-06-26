<?php

namespace App\Models;

use CodeIgniter\Model;

class PetBreedModel extends Model
{
    protected $table            = 'petbreeds';
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

    //get all the breeds regardles of species
    public function getBreeds(){
        $builder = $this->db->table('catbreeds');
        $catquery = $builder->get();

        $builder = $this->db->table('dogbreeds');
        $dogquery = $builder->get();

        $data = [
            'catbreeds' => $catquery->getResult(),
            'dogbreeds' => $dogquery->getResult(),
        ];
        return $data;
    }
}
