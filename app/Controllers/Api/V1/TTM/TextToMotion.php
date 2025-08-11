<?php

namespace App\Controllers\Api\V1\TTM;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TextToMotion extends BaseController
{
    public function index()
    {
        //
    }

    public function getCurrentApi()
    {
        // Get the current API version from the model
        $model = new \App\Models\TextToMotionModel();
        $currentApi = $model->find(1);

        if ($currentApi) {
            return $this->response->setJSON($currentApi);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND, 'API not found');
        }
    }

    public function updateCurrentApi(){
        $model = new \App\Models\TextToMotionModel();

        $input = $this->request->getJSON();
        $newIpAddress = $input->new_api ?? null;
        
        $result = $model->updateIpAddress(1, $newIpAddress);
        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'API version updated successfully',
                'new_ip_address' => $newIpAddress
            ]);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST, 'Failed to update API version');
        }
    }
}
