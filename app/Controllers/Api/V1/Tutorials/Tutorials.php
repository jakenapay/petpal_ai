<?php

namespace App\Controllers\Api\V1\Tutorials;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TutorialsModel;
use App\Models\TutorialLogsModel;

class Tutorials extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {
        $model = new TutorialsModel();
        $tutorials = $model->getTutorials();

        return $this->response->setJSON(
            [
                'message' => 'Tutorials retrieved successfully',
                'data'    => $tutorials
            ]
        );
    }

    public function getUserTutorialLogs(){
        // AUTHORIZATION CHECK
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $tutorialLogModel = new TutorialLogsModel();
        $logs = $tutorialLogModel->getUserTutorialLog($userId);
        if (!$logs) {
            return $this->response->setJSON(['message' => 'No tutorial logs found for this user']);
        }
        return $this->response->setJSON(
            [
                'message' => 'User tutorial logs retrieved successfully',
                'data'    => $logs
            ]
        );
    }

    public function completeTutorial(){
        // AUTHORIZATION CHECK
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // Get the payload
        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->response->setJSON(['error' => 'Invalid payload'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //get the tutorial id from the payload
        $tutorialId = $data['tutorial_id'] ?? null;
        if (!$tutorialId) {
            return $this->response->setJSON(
                [
                    'message' => 'Tutorial ID is required',
                ]
            );
        }

        // TUTORIAL VALIDATION
        $model = new TutorialsModel();

        //check if the tutorial exists
        $tutorial = $model->find($tutorialId);

        if (!$tutorial) {
            return $this->response->setJSON(
                [
                    'message' => 'Tutorial not found',
                ]
            );
        }

        // LOGGING THE TUTORIAL
        $tutorialLogModel = new TutorialLogsModel();

        //check if the tutorial is already completed
        $tutorialLog = $tutorialLogModel->where([
            'user_id'      => $userId,
            'tutorial_id'  => $tutorialId
        ])->first();

        if ($tutorialLog) {
            return $this->response->setJSON(
                [
                    'message' => 'Tutorial already completed',
                ]
            );
        }
        $result = $tutorialLogModel->completeUserTutorial($userId, $tutorialId);
        if (!$result) {
            return $this->response->setJSON(
                [
                    'message' => 'Failed to complete the tutorial',
                ]
            )->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON(
            [
                'message' => 'Tutorial is completed successfully!',
                'tutorial_status'    => $tutorialLogModel->getUserTutorialLog($userId)
            ]
        );
    }
}
