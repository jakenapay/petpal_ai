<?php

namespace App\Controllers\Api\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class Town extends BaseController
{
    public function index()
    {
    
    }
    public function getChest(){
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        
        $data = $this->request->getJSON(true);
        $coinAmount = $data['coin_amount'] ?? null;
        if (!$coinAmount) {
            return $this->response->setJSON(['error' => 'Coin amount is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        $userModel = new UserModel();
        
        $updateCoins = $userModel->updateCoins($userId, $coinAmount);
        
        if(!$updateCoins){
            return $this->response->setJSON([
                'message' => 'Failed to update coins',
            ])
            ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Chest was successfully opened.',
            'data' => $updateCoins
        ])
        ->setStatusCode(ResponseInterface::HTTP_OK);


    }
}
