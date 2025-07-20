<?php

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\UserModel;

class AuthController extends BaseApiController
{
    protected $modelName = 'App\Models\UserModel';

    /**
     * POST /api/auth/login
     * User login (simplified - you should implement proper password hashing)
     */
    public function login()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data['email']) || empty($data['password'])) {
            return $this->respondWithError('Email and password are required', 400);
        }

        try {
            // In a real application, you would verify the password hash
            $user = $this->model->where('email', $data['email'])->first();

            if (!$user) {
                return $this->respondUnauthorized('Invalid credentials');
            }

            // Generate a simple token (in production, use JWT or similar)
            $token = bin2hex(random_bytes(32));
            
            // In a real app, you'd store this token in database or cache
            $response = [
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'status' => $user['status']
                ],
                'token' => $token,
                'expires_in' => 3600 // 1 hour
            ];

            return $this->respondWithSuccess($response, 'Login successful');
        } catch (\Exception $e) {
            return $this->respondWithError('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/auth/register
     * User registration
     */
    public function register()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->respondWithError('No data provided', 400);
        }

        // Required fields
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->respondWithError("$field is required", 400);
            }
        }

        try {
            // In a real application, hash the password
            // $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Remove password from data for now (since we're not storing it)
            unset($data['password']);
            
            // Set default status
            $data['status'] = 'active';

            $userId = $this->model->insert($data);

            if (!$userId) {
                $errors = $this->model->errors();
                return $this->respondWithValidationError($errors);
            }

            $user = $this->model->find($userId);
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            
            $response = [
                'user' => $user,
                'token' => $token,
                'expires_in' => 3600
            ];

            return $this->respondWithSuccess($response, 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->respondWithError('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/auth/me
     * Get current user information
     */
    public function me()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        // In a real application, you would decode the JWT token to get user ID
        // For now, we'll return a mock response
        $mockUser = [
            'id' => 1,
            'name' => 'API User',
            'email' => 'api@example.com',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->respondWithSuccess($mockUser, 'User information retrieved');
    }
}