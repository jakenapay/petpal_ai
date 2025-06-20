<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\PlanModel;

class SubscriptionModel extends Model
{
    protected $table            = 'subscriptions';
    protected $primaryKey       = 'subscription_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'created_at',
        'updated_at',
        'payment_method',
        'last_payment_date',
        'next_payment_date',
        'auto_renew',


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

    // FUNCTIONS
    
    //get user subscription by user id
    public function getUserSubscription($userId)
    {
        if (!$userId) {
            return [];
        }
        $subscription = $this->where('user_id', $userId)->first();
        if (!$subscription) {
            return [];
        }
        // Convert the subscription to an array if it's not already
        if (!is_array($subscription)) {
            $subscription = $subscription->toArray();
        }
        // Ensure the subscription has a 'plan_id' key
        if (!isset($subscription['plan_id'])) {
            $subscription['plan_id'] = null; // or set a default value
        }
        //get plan details by plan id
        $planModel = new PlanModel();
        $plan = $planModel->getPlanbyId($subscription['plan_id']);
        if (!$plan) {
            return []; // Return empty array if plan not found
        }
        return $plan;
    }

    public function defaultUserSubscription($userId){
        $planModel = new PlanModel();
        //default is free so get the plan id 1
        $plan = $planModel->getPlanbyId(1);
        if (!$plan) {
            return []; // Return empty array if plan not found
        }
        //build the subscription data
        $data = [
            'user_id' => $userId,
            'plan_id' => $plan['plan_id'],
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => null, // No end date for free plan
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'payment_method' => 'free',
            'last_payment_date' => null,
            'next_payment_date' => null,
            'auto_renew' => 0
        ];
        //insert the subscription data
        $result = $this->insert($data);
        if (!$result) {
            return []; // Return empty array if insertion fails
        }
        //return the subscription data
        return $result;
    }


}
