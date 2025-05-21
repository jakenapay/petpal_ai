<?php

namespace App\Controllers\Api\V1\Users;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Profile extends BaseController
{
    public function index()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));

            $userModel = new UserModel();
            $user = $userModel->find($decoded->user_id);

            if (!$user) {
                return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            }

            unset($user['password']); // remove password

            return $this->response->setJSON([
                'user' => $user
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function update()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            $userId = $decoded->user_id;
            $userRole = $decoded->role; // Get the user's role from the decoded token

            $data = $this->request->getJSON(true);

            // Default allowed fields for the user
            $allowedFields = ['username', 'first_name', 'last_name', 'email', 'profile_image', 'last_login'];

            // Check if the user is an admin and add sensitive fields to allowed fields
            if ($userRole === 'admin') {
                $allowedFields[] = 'status';
                $allowedFields[] = 'role';
            }

            // Filter the incoming data based on the allowed fields
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            // If no valid fields are found to update
            if (empty($updateData)) {
                return $this->response->setJSON(['error' => 'No valid fields to update'])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            $userModel = new UserModel();
            $userModel->update($userId, $updateData);

            // Fetch the updated user data
            $updatedUser = $userModel->find($userId);
            unset($updatedUser['password']);

            return $this->response->setJSON(['success' => 'Profile updated successfully', 'user' => $updatedUser]);

        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }


}
