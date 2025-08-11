<?php

namespace App\Models;

use CodeIgniter\Model;

class TextToMotionModel extends Model
{
    protected $table            = 'ttm_ip_address';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ip_addr', 'old_ipaddr'];

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

    public function updateIpAddress($id, $newIpAddress)
    {
        //get the old ip address
        $oldIpAddress = $this->find($id)['ip_addr'] ?? null;
        //update the ip address
        $newip = $this->update($id, ['ip_addr' => $newIpAddress]);
        //replace the old ip address
        $oldip = $this->update($id, ['old_ipaddr' => $oldIpAddress]);
        if(!$newip){
            return false;
        }
        if(!$oldip){
            return false;
        }
        return true;
    }

}
