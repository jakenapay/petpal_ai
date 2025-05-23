<?php
use CodeIgniter\HTTP\ResponseInterface;
function authorizationCheck($request)
{
    $authHeader = $request->getHeaderLine('Authorization');
    $token = str_replace('Bearer ', '', $authHeader);
    if (!$token) {
        return null; 
    }
    try {
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->user_id ?? null;
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Token required or invalid'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        return $userId;
    } catch (\Exception $e) {
        return null;
    }
}