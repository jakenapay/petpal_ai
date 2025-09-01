<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ResetPassword extends BaseController
{
    // Constructor
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila'); // Change timezone
    }

    /**
     * Reset Password API Endpoint
     * 
     * Initiates the password reset process by generating and sending an OTP (One-Time Password) 
     * to the user's registered email address.
     * 
     * @route POST /api/v1/auth/forgot
     * 
     * @param array $json Request body containing:
     *   - email (string, required): The email address of the user requesting password reset
     * 
     * @return ResponseInterface JSON response containing:
     *   Success (200):
     *   - recipient (string): The email address where OTP was sent
     *   - otp (string): The generated 6-digit OTP code
     *   - message (string): Success confirmation message
     * 
     *   Error responses:
     *   - 400 Bad Request: When email is missing or empty
     *   - 404 Not Found: When no user exists with the provided email
     *   - 500 Internal Server Error: When email sending fails
     * 
     * @throws Exception When database operations fail
     * 
     * @description
     * - Generates a random 6-digit OTP code
     * - Sets OTP expiration to 5 minutes from generation
     * - Stores OTP and expiration in user record
     * - Sends OTP via email using sendVerificationEmail() method
     * - Returns both recipient email and OTP in response for confirmation
     * 
     * @security
     * - Validates email format and existence
     * - OTP expires after 5 minutes
     * - Email verification required before password reset
     */
    public function index(): ResponseInterface
    {
        // Reset Password
        $userModel = new UserModel();
        $json = $this->request->getJSON(true);
        $email = trim($json['email'] ?? '');
        $otpCode = random_int(100000, 999999);

        if (empty($email)) {
            return $this->response->setJSON([
                'error' => 'Email required.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $user = $userModel->where('email', $email)->first();

        if (empty($user)) {
            return $this->response->setJSON([
                'error' => 'User not found.'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Save OTP code to user's data
        $userModel->update($user['user_id'], [
            'otp' => $otpCode,
            'otp_expires' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
        ]);
        // return $this->response->setJSON(['otp' => $otp]); // Test

        // Send OTP code via email
        if (!$this->sendVerificationEmail($email, $otpCode)) {
            return $this->response->setJSON([
                'error' => 'Failed to send OTP email.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'recipient' => $email,
            'otp' => $otpCode,
            'message' => 'OTP sent successfully.',
        ]);
    }

    /**
     * Verify OTP for Password Reset
     * 
     * This endpoint verifies the One-Time Password (OTP) sent to the user's email
     * during the password reset process. Upon successful verification, the OTP
     * is cleared from the database and the user can proceed to reset their password.
     * 
     * @route POST /api/v1/auth/forgot/verify
     * @access Public
     * 
     * @param string email - User's email address (required)
     * @param string otp - 6-digit OTP code sent to user's email (required)
     * 
     * @return JSON Response
     * 
     * Success Response (200):
     * {
     *   "user_email": "user@example.com",
     *   "message": "OTP verified successfully. Proceed to reset password."
     * }
     * 
     * Error Responses:
     * - 400 Bad Request: Missing email or OTP, invalid OTP, or expired OTP
     * - 404 Not Found: User with provided email doesn't exist
     * - 500 Internal Server Error: Database operation failed
     * 
     * Example Request:
     * {
     *   "email": "user@example.com",
     *   "otp": 123456
     * }
     * 
     * @throws ValidationException When email or OTP is missing
     * @throws NotFoundException When user is not found
     * @throws ExpiredException When OTP has expired
     * @throws DatabaseException When database transaction fails
     */
    public function verifyOtp(): ResponseInterface
    {
        $userModel = new UserModel();
        $json = $this->request->getJSON(true);
        $db = \Config\Database::connect();

        $email = trim($json['email'] ?? '');                        // Email of the user
        $otp = trim($json['otp'] ?? '');                            // OTP Inserted by user
        $user = $userModel->where('email', $email)->first();    // Get user details

        if (empty($email) || empty($otp)) {
            return $this->response->setJSON(['error' => 'Email or OTP are required.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (empty($user)) {
            return $this->response->setJSON(['error' => 'User not found.'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        if ($user['otp'] !== $otp) {
            return $this->response->setJSON(['error' => 'Invalid OTP code.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (strtotime($user['otp_expires']) < time()) {
            return $this->response->setJSON(['error' => 'Your OTP code has expired. Please request a new one.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $tokenVerification = random_int(000000, 999999); // Generate new OTP for security of reset Password
        $db->transStart();
        if (!$userModel->update($user['user_id'], ['otp' => $tokenVerification, 'otp_expires' => date('Y-m-d H:i:s', strtotime('+5 minutes'))])) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to verify OTP. Please try again.'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $db->transComplete();
        return $this->response->setJSON([
            'user_email' => $email,
            'token' => $tokenVerification, // Return token for verification in reset password
            'message' => 'OTP verified successfully. Proceed to reset password.'
        ]);
    }

    /**
     * Reset user password
     * 
     * Allows users to reset their password by providing email and new password.
     * Validates input, checks if user exists, and updates password in database.
     * 
     * @method POST
     * @route /api/v1/auth/forgot/reset
     * 
     * @param string email User's email address (required)
     * @param string newPassword New password for the user (required)
     * @param string confirmNewPassword Confirmation of new password (required)
     * 
     * @return JSON
     * 
     * Success Response (200):
     * {
     *   "user_email": "user@example.com",
     *   "message": "Password reset successfully. You can now log in with your new password."
     * }
     * 
     * Error Responses:
     * 400 Bad Request:
     * - Missing required fields: {"error": "Email, new password, and confirm password are required."}
     * - Password mismatch: {"error": "New password and confirm password do not match."}
     * - Validation errors: {"error": {"newPassword": "The newPassword field is required.", ...}}
     * 
     * 404 Not Found:
     * - User not found: {"error": "User not found."}
     * 
     * 500 Internal Server Error:
     * - Database error: {"error": "Failed to reset password. Please try again."}
     * 
     * @throws Exception When database transaction fails
     */
    public function resetPassword(): ResponseInterface
    {
        $userModel = new UserModel();
        $json = $this->request->getJSON(true);

        $email = trim($json['email'] ?? '');
        $newPassword = trim($json['newPassword'] ?? '');
        $confirmNewPassword = trim($json['confirmNewPassword'] ?? '');
        $tokenVerification = trim($json['tokenVerification'] ?? ''); // Token from verifyOtp response

        // Validate input for new Password and confirm new password
        if (
            $this->validate([
                'newPassword' => 'required|min_length[8]',
                'confirmNewPassword' => 'required|min_length[8]|matches[newPassword]',
                'tokenVerification' => 'required|integer'
            ]) === false
        ) {
            return $this->response->setJSON(['error' => $this->validator->getErrors()])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (empty($email) || empty($newPassword) || empty($confirmNewPassword) || empty($tokenVerification)) {
            return $this->response->setJSON(['error' => 'Email, token, new password, and confirm password are required.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($userModel->where('email', $email)->first() === null) {
            return $this->response->setJSON(['error' => 'User not found.'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $user = $userModel->where('email', $email)->first();    // Get user details
        if ($user['otp'] !== $tokenVerification) {
            return $this->response->setJSON(['error' => 'Invalid session code.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (strtotime($user['otp_expires']) < time()) {
            return $this->response->setJSON(['error' => 'Session expired. Please try again later.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($newPassword !== $confirmNewPassword) {
            return $this->response->setJSON(['error' => 'New password and confirm password do not match.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $db = \Config\Database::connect();
        $db->transStart();

        if (!$userModel->update($user['user_id'], ['password' => $hashedPassword])) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to reset password. Please try again.'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$userModel->update($user['user_id'], ['otp' => NULL, 'otp_expires' => NULL])) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to clear verification session. Please try again.'])
            ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $db->transComplete();
        return $this->response->setJSON([
            'user_email' => $email,
            'message' => 'Password reset successfully. You can now log in with your new password.'
        ]);

    }

    private function sendVerificationEmail($userEmail, $verificationCode)
    {
        $emailService = \Config\Services::email();
        $emailService->setTo($userEmail);
        $emailService->setSubject('PetPal - Password Reset OTP');
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
                    <h1>Password Reset OTP Code</h1>
                    <p>We received a request to reset your password for your PetPal account. Please use the OTP code below to proceed with your password reset:</p>
                    <div class="code">' . $verificationCode . '</div>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <p class="footer">This OTP code will expire in 5 minutes.</p>
                </div>
            </body>
            </html>
        ');

        if (!$emailService->send()) {
            return false;
        } else {
            return true;
        }
    }
}
