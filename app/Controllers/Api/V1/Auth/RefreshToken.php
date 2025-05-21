<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class RefreshToken extends BaseController
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

            $newPayload = [
                'iat'     => time(),
                'exp'     => time() + 3600,
                'user_id' => $decoded->user_id ?? null,
                'role'    => $decoded->role ?? null,
            ];

            $newToken = JWT::encode($newPayload, getenv('JWT_SECRET'), 'HS256');

            return $this->response->setJSON([
                'token' => $newToken,
                'user_id' => $decoded->user_id ?? null,
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }
}
