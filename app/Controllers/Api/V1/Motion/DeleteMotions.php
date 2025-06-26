<?php

namespace App\Controllers\Api\V1\Motion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SavedMotionsModel;

class DeleteMotions extends BaseController
{
    public function index($motionId = null)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $savedMotionsModel = new SavedMotionsModel();

        // $data = $this->request->getJSON(true);
        // if (!$data) {
        //     return $this->response->setJSON(['error' => 'Invalid request data'])
        //         ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        // }

        // $motionId = $data['motion_id'];
        if (!$motionId) {
            return $this->response->setJSON(['error' => 'Motion ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //get the details of the motion to be deleted
        $motionDetails = $savedMotionsModel->getMotionById($motionId);
        if (!$motionDetails) {
            return $this->response->setJSON(['error' => 'Motion not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $result = $savedMotionsModel->deleteMotion($motionId);
        if (!$result) {
            return $this->response->setJSON(['error' => 'Failed to delete motion'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Motion deleted successfully',
            'deleted_data' => $motionDetails
        ]);

    }
}
