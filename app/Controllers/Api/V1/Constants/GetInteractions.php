<?php

namespace App\Controllers\Api\V1\Constants;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\InteractionTypeModel;
use App\Models\InteractionCategoriesModel;

class GetInteractions extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        $interactionModel = new InteractionTypeModel();
        $interactions = $interactionModel->findAll();
        if (!$interactions) {
            return $this->response->setJSON(['error' => 'No interactions found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Interactions retrieved successfully',
            'data' => $interactions
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function CategorizedInteractions($category_id, $interaction_id = null)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $interactionModel = new InteractionCategoriesModel();
        $interactions = $interactionModel->getInteractionCategoryById($category_id, $interaction_id);
        if (!$interactions) {
            return $this->response->setJSON(['error' => 'No interactions found for this category'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        if (!$interactions) {
            return $this->response->setJSON(['error' => 'No interactions found for this category'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Interaction/s category was retrieved successfully',
            'data' => $interactions
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function InteractionCategories(){
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $interactionCategoryModel = new InteractionCategoriesModel();
        $interactionCategories = $interactionCategoryModel->getInteractionCategories();
        if (!$interactionCategories) {
            return $this->response->setJSON(['error' => 'No interaction categories found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Interaction categories retrieved successfully',
            'data' => $interactionCategories
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
