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
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Your account is ' . $user['status'] . '. Please contact support.']);
        }

        // Insert last login time 
        $userModel->update($user['user_id'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        // Generate JWT token
        $token = $this->generateJWT($user);

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
            'success' => 'Login successful',
            'user_id' => $user['user_id'],
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
