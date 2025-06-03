<?php

namespace App\Controllers\Api\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SubscriptionModel;

class GetUserSubscription extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Fetch user subscription details
        $subscriptionModel = new SubscriptionModel();
        $subscription = $subscriptionModel->getUserSubscription($userId);

        if (!$subscription) {
            return $this->response->setJSON(['error' => 'No subscription found for this user'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Return the subscription details
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User subscription retrieved successfully',
            'data' => [
                'subscription' => $subscription
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
