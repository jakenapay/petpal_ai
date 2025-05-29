<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemTransactionsModel extends Model
{
    protected $table            = 'item_transactions';
    protected $primaryKey       = 'transactions_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'item_id',
        'quantity',
        'transaction_type',
        'coins_spent',
        'transaction_date'
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

    public function insertTransaction($data){
        log_message('info', 'Transaction data: ' . json_encode($data));
        return $this->insert($data);
    }
    public function getLastTransaction($user_id){
        return $this->where('user_id', $user_id)
            ->orderBy('transaction_date', 'DESC')
            ->first();
    }
}
