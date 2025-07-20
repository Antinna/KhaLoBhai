<?php

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\UserModel;

class UsersController extends BaseApiController
{
    protected $modelName = 'App\Models\UserModel';

    /**
     * GET /api/users
     * Get all users with pagination
     */
    public function index()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 10;
        $search = $this->request->getGet('search');

        try {
            if ($search) {
                $users = $this->model->searchUsers($search);
                $data = [
                    'users' => $users,
                    'total' => count($users),
                    'page' => 1,
                    'limit' => count($users)
                ];
            } else {
                $users = $this->model->getUsersPaginated((int)$page, (int)$limit);
                $pager = $this->model->pager;
                
                $data = [
                    'users' => $users,
                    'pagination' => [
                        'current_page' => $pager->getCurrentPage(),
                        'per_page' => $pager->getPerPage(),
                        'total' => $pager->getTotal(),
                        'total_pages' => $pager->getPageCount(),
                        'has_next' => $pager->getCurrentPage() < $pager->getPageCount(),
                        'has_previous' => $pager->getCurrentPage() > 1,
                        'next_page' => $pager->getCurrentPage() < $pager->getPageCount() ? $pager->getCurrentPage() + 1 : null,
                        'previous_page' => $pager->getCurrentPage() > 1 ? $pager->getCurrentPage() - 1 : null
                    ]
                ];
            }

            return $this->respondWithSuccess($data, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/users/{id}
     * Get a specific user
     */
    public function show($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        if (!$id) {
            return $this->respondWithError('User ID is required', 400);
        }

        try {
            $user = $this->model->find($id);
            
            if (!$user) {
                return $this->respondNotFound('User not found');
            }

            return $this->respondWithSuccess($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/users
     * Create a new user
     */
    public function create()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->respondWithError('No data provided', 400);
        }

        try {
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            $userId = $this->model->insert($data);

            if (!$userId) {
                $errors = $this->model->errors();
                return $this->respondWithValidationError($errors);
            }

            $user = $this->model->find($userId);
            return $this->respondWithSuccess($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/users/{id}
     * Update a user
     */
    public function update($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        if (!$id) {
            return $this->respondWithError('User ID is required', 400);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if (empty($data)) {
            return $this->respondWithError('No data provided', 400);
        }

        try {
            $existingUser = $this->model->find($id);
            if (!$existingUser) {
                return $this->respondNotFound('User not found');
            }

            $updated = $this->model->update($id, $data);

            if (!$updated) {
                $errors = $this->model->errors();
                return $this->respondWithValidationError($errors);
            }

            $user = $this->model->find($id);
            return $this->respondWithSuccess($user, 'User updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/users/{id}
     * Delete a user
     */
    public function delete($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        if (!$id) {
            return $this->respondWithError('User ID is required', 400);
        }

        try {
            $user = $this->model->find($id);
            if (!$user) {
                return $this->respondNotFound('User not found');
            }

            $deleted = $this->model->delete($id);

            if (!$deleted) {
                return $this->respondWithError('Failed to delete user', 500);
            }

            return $this->respondWithSuccess(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/users/active
     * Get only active users
     */
    public function active()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        try {
            $users = $this->model->getActiveUsers();
            return $this->respondWithSuccess($users, 'Active users retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve active users: ' . $e->getMessage(), 500);
        }
    }
}