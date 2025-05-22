<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use \Firebase\JWT\JWT;

class Login extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
    }

    public function index()
    {
        // Retrieve JSON payload and decode into associative array
        $json = $this->request->getJSON(true);

        // Retrieve, trim and sanitize input
        $username = isset($json['username']) ? htmlspecialchars(trim($json['username']), ENT_QUOTES, 'UTF-8') : '';
        $password = $json['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Username and password are required']);
        }

        $userModel = new \App\Models\UserModel();

        // Check if user exists
        $user = $userModel->where('username', $username)->first();
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid username or password']);
        }

        // Check account status
        if (in_array($user['status'], ['inactive', 'suspended'])) {

            // get user's email by username
            $userEmail = $userModel->where('username', $username)->first()['email'];

            return $this->response
            ->setStatusCode(403)
            ->setJSON([
                'error' => 'Your account is ' . $user['status'] . '. Please contact support.',
                'email' => $userEmail,
            ]);
        }

        // Insert last login time 
        $userModel->update($user['user_id'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        // Generate JWT token
        $token = $this->generateJWT($user);

        // Check if user has a pet
        $petModel = new \App\Models\PetModel();
        $petModel->where('user_id', $user['user_id']);
        $count = $petModel->countAllResults();

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
            'success' => 'Login successful',
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'status' => $user['status'],
            'last_login' => $user['last_login'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
            'profile_img' => $user['profile_image'],
            'mbti' => $user['mbti'],
            'coins' => $user['coins'],
            'user_grade' => $user['user_grade'],
            'pet_count' => $count,
            'token'   => $token,
            ]);
    }

    private function generateJWT($user)
    {
        $key = getenv('JWT_SECRET');
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $user['user_id'],
            'role' => $user['role'],
        ];

        return JWT::encode($payload, $key, 'HS256');
    }
}
