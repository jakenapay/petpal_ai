<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class API extends BaseController
{
    public function index()
    {
        //
    }

    public function handleLogin()
    {
        $userModel = new \App\Models\UserModel();

        // Retrieve, trim and sanitize input
        $username = htmlspecialchars(trim($this->request->getPost('username')), ENT_QUOTES, 'UTF-8');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->response
                        ->setStatusCode(400)
                        ->setJSON(['error' => 'Username and password are required']);
        }

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

        // Set session data (if sessions are being used)
        session()->set([
            'user_id'      => $user['user_id'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'first_name'   => $user['first_name'],
            'last_name'    => $user['last_name'],
            'profile_image'=> $user['profile_image'],
            'status'       => $user['status'],
            'role'         => $user['role'],
            'logged_in'    => true
        ]);

        return $this->response
                    ->setStatusCode(200)
                    ->setJSON([
                        'success' => 'Login successful',
                        'user' => [
                            'user_id'      => $user['user_id'],
                            'username'     => $user['username'],
                            'email'        => $user['email'],
                            'first_name'   => $user['first_name'],
                            'last_name'    => $user['last_name'],
                            'profile_image'=> $user['profile_image'],
                            'status'       => $user['status'],
                            'role'         => $user['role']
                        ]
                    ]);
    }
}
