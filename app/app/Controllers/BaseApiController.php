<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class BaseApiController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';
    protected $modelName;
    protected $model;

    public function __construct()
    {
        // Load the model if specified
        if ($this->modelName) {
            $this->model = model($this->modelName);
        }
    }

    /**
     * Return standardized success response
     */
    protected function respondWithSuccess($data = null, string $message = 'Success', int $code = 200)
    {
        return $this->respond([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return standardized error response
     */
    protected function respondWithError(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }

    /**
     * Return validation error response
     */
    protected function respondWithValidationError($errors)
    {
        return $this->respondWithError('Validation failed', 422, $errors);
    }

    /**
     * Return not found response
     */
    protected function respondNotFound(string $message = 'Resource not found')
    {
        return $this->respondWithError($message, 404);
    }

    /**
     * Return unauthorized response
     */
    protected function respondUnauthorized(string $message = 'Unauthorized')
    {
        return $this->respondWithError($message, 401);
    }

    /**
     * Return forbidden response
     */
    protected function respondForbidden(string $message = 'Forbidden')
    {
        return $this->respondWithError($message, 403);
    }

    /**
     * Validate API key (simple implementation)
     */
    protected function validateApiKey(): bool
    {
        $apiKey = $this->request->getHeaderLine('X-API-Key');
        $validApiKey = env('API_KEY', 'your-secret-api-key');
        
        return $apiKey === $validApiKey;
    }

    /**
     * Check if request is authenticated
     */
    protected function isAuthenticated(): bool
    {
        // Simple API key authentication
        return $this->validateApiKey();
    }

    /**
     * Require authentication for the request
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            return $this->respondUnauthorized('API key required');
        }
        return null;
    }
}