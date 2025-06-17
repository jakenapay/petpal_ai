<?php

namespace App\Models;

use CodeIgniter\Model;

class DiamondPackagesPurchaseHistoryModel extends Model
{
    protected $table            = 'diamond_package_purchases';
    protected $primaryKey       = 'purchase_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'package_id',
        'diamonds_purchased',
        'bonus_diamonds_received',
        'total_diamonds_received',
        'real_price_paid',
        'currency',
        'payment_method',
        'payment_transaction_id',
        'platform',
        'purchase_date',
        'status',
        'receipt_data',
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

    public function logPurchase($data)
    {
        // Customize the insert logic if needed
        return $this->insert($data);
    }
    public function getAllTransactions($userId){
        return $this->where('user_id', $userId)->findAll();
    }
}
