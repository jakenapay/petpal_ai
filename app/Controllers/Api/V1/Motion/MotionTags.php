<?php

namespace App\Controllers\Api\V1\Motion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MotionTagsModel;

class MotionTags extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $motionTagsModel = new MotionTagsModel();
        $motionTags = $motionTagsModel->getAllMotionTags();
        if (!$motionTags) {
            return $this->response->setJSON(['error' => 'No Motion Tags found'])
                ->setStatusCode(ResponseInterface::HTTP_OK);
        }
        return $this->response->setJSON(
            [
                'message' => 'Motion Tags retrieved successfully',
                'data' => $motionTags
            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
