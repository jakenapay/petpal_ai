<?php
namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Email\Email;
use App\Models\DefaultItemsModel;
use App\Models\InventoryModel;
use App\Models\SubscriptionModel;
class Register extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
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
        $birthMonth      = trim($json['birth_month'] ?? '');
        $birthDay        = trim($json['birth_day'] ?? '');
        $birthYear       = trim($json['birth_year'] ?? '');
        $gender          = trim($json['gender'] ?? '');

        if (!$email || !$password || !$confirmPassword || !$username || !$firstName || !$lastName || !$birthMonth || !$birthDay || !$birthYear || !$gender) {
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

        if ($userModel->where('email', $email)->first()) {
            return $this->response->setJSON([
            'error' => 'Email already exists.'
            ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }
        
        if ($userModel->where('username', $username)->first()) {
            return $this->response->setJSON([
            'error' => 'Username already exists.'
            ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }
        

        $verificationCode = random_int(100000, 999999);

        $userData = [
            'email'      => $email,
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'status'     => 'inactive',
            'role'       => 'user',
            'gender'     => $gender,
            'birth_date' => date('Y-m-d', strtotime("$birthYear-$birthMonth-$birthDay")),
            'verification_code' => $verificationCode,
            'verification_expiration_date' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (!$userId = $userModel->insert($userData)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'error' => 'Failed to register user.'
                ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!$this->setDefaultItems($userId)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'error' => 'Failed to add default items.'
                ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!$this->defaultSubscriptions($userId)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'error' => 'Failed to set default subscription.'
                ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!$this->sendVerificationEmail($email, $verificationCode)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'error' => 'Failed to send verification email.'
                ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }


            $db->transComplete();
            
        } catch (\Exception $e) { 
            log_message('error', 'Registration error: ' . $e->getMessage());
            $db->transRollback();          
            return $this->response->setJSON([
                'error' => 'Registration failed. Please try again.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($db->transStatus() === FALSE) {
            return $this->response->setJSON([
                'error' => 'Transaction failed, please try again.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'success' => 'User registered successfully'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    private function sendVerificationEmail($userEmail, $verificationCode)
    {
        $emailService = \Config\Services::email();
        $emailService->setTo($userEmail);
        $emailService->setSubject('Aipet - Email Verification');
        $emailService->setMessage('
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        padding: 20px;
                        color: #333;
                    }
                    .container {
                        background-color: #fff;
                        padding: 30px;
                        border-radius: 8px;
                        max-width: 600px;
                        margin: auto;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    }
                    h1 {
                        color: #007BFF;
                    }
                    p {
                        font-size: 16px;
                    }
                    .code {
                        font-size: 28px;
                        font-weight: bold;
                        color: #28a745;
                        background-color: #e9f8ee;
                        padding: 15px 25px;
                        display: inline-block;
                        border-radius: 6px;
                        letter-spacing: 4px;
                        margin: 20px 0;
                    }
                    .footer {
                        font-size: 12px;
                        color: #777;
                        margin-top: 30px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Email Verification</h1>
                    <p>Thank you for registering with Aipet. To complete your registration, please use the verification code below:</p>
                    <div class="code">' . $verificationCode . '</div>
                    <p>If you did not request this email, please ignore it.</p>
                    <p class="footer">This code will expire in 5 minutes.</p>
                </div>
            </body>
            </html>
        ');

        if (!$emailService->send()) {
            return false;
        }
        else{
            return true;
        }
    }

    public function verifyEmail()
    {
        $json = $this->request->getJSON(true);
        $email = trim($json['email'] ?? '');
        $verificationCode = trim($json['verification_code'] ?? '');
        $userModel = new UserModel();

        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            return $this->response->setJSON([
                'error' => 'Email not found. Please enter a valid email.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($user['verification_code'] !== $verificationCode) {
            return $this->response->setJSON([
                'error' => 'Invalid verification code. Please check your code and try again.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (strtotime($user['verification_expiration_date']) < time()) {
            return $this->response->setJSON([
                'error' => 'Verification code has expired. Please request a new one.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userModel->update($user['user_id'], [
            'status' => 'active',
            'verification_code' => null,
            'verification_expiration_date' => null,
        ]);

        return $this->response->setJSON([
            'success' => 'Email verified successfully.'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }


    public function resendVerificationCode()
    {
        $json = $this->request->getJSON(true);
        $email = trim($json['email'] ?? '');

        if (!$email) {
            return $this->response->setJSON([
                'error' => 'Email is required.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON([
                'error' => 'User not found.'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        if ($user['status'] !== 'inactive') {
            return $this->response->setJSON([
                'error' => 'User is already verified.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $lastSent = strtotime($user['verification_expiration_date'] ?? '1970-01-01') - 300;
        $now = time();

        if (($now - $lastSent) < 60) { 
            return $this->response->setJSON([
                'error' => 'Please wait before requesting another code.'
            ])->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS);
        }

        $newVerificationCode = random_int(100000, 999999);

        $userModel->update($user['user_id'], [
            'verification_code' => $newVerificationCode,
            'verification_expiration_date' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
        ]);

        if (!$this->sendVerificationEmail($email, $newVerificationCode)) {
            return $this->response->setJSON([
                'error' => 'Failed to send verification email.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'success' => 'Verification code resent successfully.'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function setDefaultItems($user_id){
        //life stage id is default to 1 since this is registration.
        $life_stage_id = 2;
        $defaultItemsModel = new DefaultItemsModel();
        $defaultItems = $defaultItemsModel->getDefaultItems($life_stage_id);
        
        if (empty($defaultItems || !$defaultItems)) {
            return false;
        }
        //access the user inventory and add the default items to it.
        $inventoryModel = new InventoryModel();
        $result = $inventoryModel->addDefaultItems($user_id, $defaultItems);
        if (!$result) {
            return false; 
        }
        return true;
    }

    public function defaultSubscriptions($userId){
        $subscriptionModel = new SubscriptionModel();
        $defaultSubscriptions = $subscriptionModel->defaultUserSubscription($userId);
        if (!$defaultSubscriptions) {
            return false;
        }
        return true;
    }
}
