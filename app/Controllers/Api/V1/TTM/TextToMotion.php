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
}
