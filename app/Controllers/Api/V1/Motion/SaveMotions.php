<?php

namespace App\Controllers\Api\V1\Motion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SavedMotionsModel;
class SaveMotions extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->response->setJSON(['error' => 'Invalid request data'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pet_id = $data['pet_id'];
        if (!$pet_id) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $motionTags = $data['motion_tags'];
        if (!$motionTags) {
            return $this->response->setJSON(['error' => 'Motion tags are required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $motion_name = $data['motion_name'];
        if (!$motion_name) {
            return $this->response->setJSON(['error' => 'Motion name is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        //build the payload.
        $payload = [
            'user_id' => $userId,
            'pet_id' => $pet_id,
            'motion_name' => $motion_name ?? "Not Provided",
            "original_prompt" => $data['original_prompt'] ?? "Not Provided",
            'motion_tags' => json_encode($motionTags),
            "motion_category" => $data['motion_category'] ?? "No Table to get category from",
            "difficulty_level" => $data['difficulty_level'] ?? "N/A",
            "bvh_data" => $data['bvh_data'] ?? "N/A",
            "bvh_duration_seconds" => $data['bvh_duration_seconds'] ?? 0.00,
            "bvh_frames_count" => $data['bvh_frames_count'] ?? 0,
            "is_favorite" => $data['is_favorite'] ?? 0,
            "user_rating" => $data['user_rating'] ?? null,
            "quality_score" => $data['quality_score'] ?? 0.00,
            "is_public" => $data['is_public'] ?? 0,
            "is_sharable" => $data['is_sharable'] ?? 0,
            "share_count" => $data['share_count'] ?? 0,

        ];
        $db = \Config\Database::connect();
        // $db->transStart();

        //send the payload to the model function
        $savedMotionsModel = new SavedMotionsModel();
        $result = $savedMotionsModel->handleSaveMotions($userId, $payload);
        log_message('error', "Result: " . $result);
        if (!$result) {
            // $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to save motion'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        

        //reduce user coins.

        //reduce pet energy
        // $db->transCommit();
        return $this->response->setJSON(['message' => 'Motion saved successfully'])
            ->setStatusCode(ResponseInterface::HTTP_OK);


    }
}
