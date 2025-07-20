<?php

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;

class ApiController extends BaseApiController
{
    /**
     * GET /api/
     * API Information and health check
     */
    public function index()
    {
        $data = [
            'name' => 'CodeIgniter 4 API',
            'version' => '1.0.0',
            'description' => 'RESTful API built with CodeIgniter 4',
            'status' => 'active',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'users' => [
                    'GET /api/users' => 'Get all users (paginated)',
                    'GET /api/users/{id}' => 'Get specific user',
                    'POST /api/users' => 'Create new user',
                    'PUT /api/users/{id}' => 'Update user',
                    'DELETE /api/users/{id}' => 'Delete user',
                    'GET /api/users/active' => 'Get active users only'
                ],
                'posts' => [
                    'GET /api/posts' => 'Get all posts (paginated)',
                    'GET /api/posts/{id}' => 'Get specific post',
                    'POST /api/posts' => 'Create new post',
                    'PUT /api/posts/{id}' => 'Update post',
                    'DELETE /api/posts/{id}' => 'Delete post'
                ],
                'auth' => [
                    'POST /api/auth/login' => 'User login',
                    'POST /api/auth/register' => 'User registration',
                    'GET /api/auth/me' => 'Get current user info'
                ]
            ],
            'authentication' => [
                'type' => 'API Key',
                'header' => 'X-API-Key',
                'description' => 'Include your API key in the X-API-Key header'
            ]
        ];

        return $this->respondWithSuccess($data, 'API is running successfully');
    }
}