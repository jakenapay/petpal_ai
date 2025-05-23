<?php
function authorizationCheck($request)
{
    $authHeader = $request->getHeaderLine('Authorization');
    $token = str_replace('Bearer ', '', $authHeader);
    if (!$token) {
        return null; 
    }
    try {
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(getenv('JWT_SECRET'), 'HS256'));
        return $decoded->user_id ?? null;
    } catch (\Exception $e) {
        return null;
    }
}