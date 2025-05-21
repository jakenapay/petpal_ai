<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseController
{
    public function index()
    {
        //
    }

    public function profile()
    {
        $userModel = new \App\Models\UserModel();
        $userId = session()->get('user_id');
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User not found');
        }

        return view('user/profile', ['user' => $user]);
    }

    public function editProfile()
    {
        // Only allow POST requests
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/profile');
        }

        $userModel = new \App\Models\UserModel();
        $userId = $this->request->getPost('user_id');

        // Gather form data
        $data = [
            'email'      => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'status'     => $this->request->getPost('status')
            // 'role' is read-only; no update needed
        ];

        // If password was provided, hash and update it
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Handle profile image upload if available
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Check for a current image and delete it if exists
            $currentUser = $userModel->find($userId);
            if (!empty($currentUser) && !empty($currentUser['profile_image'])) {
                $currentImagePath = FCPATH . 'assets/images/users/' . basename($currentUser['profile_image']);
                if (file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }
            
            // Generate a new file name and move the file into the users images directory
            $newName = $file->getRandomName();
            $destinationPath = FCPATH . 'assets/images/users/';
            $file->move($destinationPath, $newName);
            // Update the profile image path
            $data['profile_image'] = base_url('assets/images/users/' . $newName);
        }
        
        // Attempt to update the user data in the database
        if ($userModel->update($userId, $data)) {
            return redirect()->to('profile')->with('success', 'Profile updated successfully');
        } else {
            return redirect()->to('profile')->with('error', 'Unable to update profile');
        }
    }
}
