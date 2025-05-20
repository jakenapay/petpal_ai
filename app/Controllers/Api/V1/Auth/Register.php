<?php
namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Register extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        $json = $this->request->getJSON(true);

        $email           = trim($json['email'] ?? '');
        $password        = $json['password'] ?? '';
        $confirmPassword = $json['confirm_password'] ?? '';
        $username        = trim($json['username'] ?? '');
        $firstName       = trim($json['first_name'] ?? '');
        $lastName        = trim($json['last_name'] ?? '');

        if (!$email || !$password || !$confirmPassword || !$username || !$firstName || !$lastName) {
            return $this->response->setJSON([
                'error' => 'All fields are required.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Validate username: only letters and numbers allowed
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return $this->response->setJSON([
                'error' => 'Username must contain only letters and numbers.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Validate first name: only letters allowed
        if (!preg_match('/^[a-zA-Z]+$/', $firstName)) {
            return $this->response->setJSON([
                'error' => 'First name must contain only letters.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Validate last name: only letters allowed
        if (!preg_match('/^[a-zA-Z]+$/', $lastName)) {
            return $this->response->setJSON([
                'error' => 'Last name must contain only letters.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (strlen($password) < 8) {
            return $this->response->setJSON([
                'error' => 'Password must be at least 8 characters long.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($password !== $confirmPassword) {
            return $this->response->setJSON([
                'error' => 'Password and confirm password do not match.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userModel = new UserModel();

        if ($userModel->where('email', $email)->orWhere('username', $username)->first()) {
            return $this->response->setJSON([
                'error' => 'Email or username already exists.'
            ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }

        $userData = [
            'email'      => $email,
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'status'     => 'inactive',
            'role'       => 'user',
        ];

        $userId = $userModel->insert($userData);

        if (!$userId) {
            return $this->response->setJSON([
                'error' => 'Registration failed.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        

        return $this->response->setJSON([
            'success' => 'User registered successfully'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
