<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function index()
    {
        //
    }

    public function register()
    {
        return view('auth/register');
    }

    public function login()
    {
        helper('url');
        return view('auth/login');
    }

    public function registerUser()
    {
        $userModel = new \App\Models\UserModel();

        // Retrieve, trim and sanitize input
        $username = htmlspecialchars(trim($this->request->getPost('username')), ENT_QUOTES, 'UTF-8');
        if (strlen($username) <= 8 || strpos($username, ' ') !== false || preg_match('/[^a-zA-Z0-9_]/', $username)) {
            return redirect()->back()->with('error', 'Please ensure your username is longer than 8 characters, has no spaces, and contains only letters, numbers, or underscores.');
        }
        $email = filter_var(trim($this->request->getPost('email')), FILTER_SANITIZE_EMAIL);
        $password = $this->request->getPost('password');
        $pass_confirm = $this->request->getPost('pass_confirm');
        $first_name = htmlspecialchars(trim($this->request->getPost('first_name')), ENT_QUOTES, 'UTF-8');
        if (preg_match('/[^a-zA-Z]/', $first_name)) {
            return redirect()->back()->with('error', 'Invalid characters in first name');
        }
        $last_name = htmlspecialchars(trim($this->request->getPost('last_name')), ENT_QUOTES, 'UTF-8');
        if (preg_match('/[^a-zA-Z]/', $last_name)) {
            return redirect()->back()->with('error', 'Invalid characters in last name');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid email address');
        }

        // Validate password confirmation
        if ($password !== $pass_confirm) {
            return redirect()->back()->with('error', 'Passwords do not match');
        }

        // Create data array with bcrypt password hashing
        $data = [
            'username'   => $username,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $first_name,
            'last_name'  => $last_name
        ];

        if ($userModel->insert($data)) {
            return redirect()->to('/login')->with('success', 'User registered successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to register user');
        }
    }

    public function loginUser()
    {
        $userModel = new \App\Models\UserModel();

        // Retrieve, trim and sanitize input
        $username = htmlspecialchars(trim($this->request->getPost('username')), ENT_QUOTES, 'UTF-8');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($username) || empty($password)) {
            return redirect()->back()->with('error', 'Username and password are required');
        }

        // Check if user exists
        $user = $userModel->where('username', $username)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Invalid username or password');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid username or password');
        }

        if ($user['status'] === 'inactive' || $user['status'] === 'suspended') {
            return redirect()->to('/login')->with('error', 'Your account is ' . $user['status'] . '. Please contact support.');
        }

        // Set session data
        session()->set([
            'user_id'   => $user['user_id'],
            'username'  => $user['username'],
            'email'     => $user['email'],
            'first_name'=> $user['first_name'],
            'last_name' => $user['last_name'],
            'profile_image' => $user['profile_image'],
            'status' => $user['status'],
            'role' => $user['role'],
            'logged_in' => true
        ]);

        return redirect()->to('main')->with('success', 'Login successful');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login')->with('success', 'Logged out successfully');
    }

}
