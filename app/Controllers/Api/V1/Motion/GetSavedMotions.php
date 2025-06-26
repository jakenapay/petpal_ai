<?php

namespace App\Controllers\Api\V1\Motion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SavedMotionsModel;

class GetSavedMotions extends BaseController
{
    public function index($motionTags = null)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;
        $motionTagsJson = $this->request->getGet('motion_tag_id');
        $pet_Id = $this->request->getGet('pet_id');

        $motionTags = json_decode($motionTagsJson, true);


        $savedMotionsModel = new SavedMotionsModel();

        $savedMotions = $savedMotionsModel->getSavedMotionsByUserId($userId, $motionTags, $pet_Id);
        
        return $this->response->setJSON([
            'message' => 'Motions retrieved successfully',
            'data' => $savedMotions
        ]);
    }
}
