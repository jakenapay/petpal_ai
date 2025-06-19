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
        $motionTagsJson = $this->request->getGet('motion_id');
        $pet_Id = $this->request->getGet('pet_id');


        // if (!$motionTagsJson) {
        //     return $this->response->setJSON(['error' => 'Invalid request.'])
        //         ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        // }

        // if (!$pet_Id) {
        //     return $this->response->setJSON(['error' => 'Invalid request. Pet id is required.'])
        //         ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        // }

        $motionTags = json_decode($motionTagsJson, true);


        $savedMotionsModel = new SavedMotionsModel();
        $savedMotions = $savedMotionsModel->getSavedMotionsByUserId($userId, $motionTags, $pet_Id);

        return $this->response->setJSON([
            'message' => 'Motions retrieved successfully',
            'data' => $savedMotions
        ]);
    }
}
