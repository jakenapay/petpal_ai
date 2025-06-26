<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetBreedModel;
class GetBreeds extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $breeds = (new PetBreedModel())->getBreeds();
        if (!$breeds) {
            return $this->response->setJSON(['error' => 'No breeds found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON(['breeds' => $breeds])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
