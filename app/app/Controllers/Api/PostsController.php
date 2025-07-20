<?php

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\PostModel;

class PostsController extends BaseApiController
{
    protected $modelName = 'App\Models\PostModel';

    /**
     * GET /api/posts
     * Get all posts with pagination
     */
    public function index()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck)
            return $authCheck;

        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 10;
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        try {
            $query = $this->model;

            if ($search) {
                $posts = $this->model->searchPosts($search);
            } elseif ($status === 'published') {
                $posts = $this->model->getPublishedPosts();
            } else {
                $posts = $this->model->getPostsWithUser();
            }

            // Apply pagination if not searching
            if (!$search && !$status) {
                $posts = $this->model->paginate((int) $limit, 'default', (int) $page);
                $pager = $this->model->pager;

                $data = [
                    'posts' => $posts,
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
            } else {
                $data = [
                    'posts' => $posts,
                    'total' => count($posts)
                ];
            }

            return $this->respondWithSuccess($data, 'Posts retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve posts: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/posts/{id}
     * Get a specific post
     */
    public function show($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck)
            return $authCheck;

        if (!$id) {
            return $this->respondWithError('Post ID is required', 400);
        }

        try {
            $post = $this->model->select('posts.*, users.name as author_name, users.email as author_email')
                ->join('users', 'users.id = posts.user_id', 'left')
                ->find($id);

            if (!$post) {
                return $this->respondNotFound('Post not found');
            }

            return $this->respondWithSuccess($post, 'Post retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/posts
     * Create a new post
     */
    public function create()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck)
            return $authCheck;

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->respondWithError('No data provided', 400);
        }

        try {
            // Set default values
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            if ($data['status'] === 'published' && !isset($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            $postId = $this->model->insert($data);

            if (!$postId) {
                $errors = $this->model->errors();
                return $this->respondWithValidationError($errors);
            }

            $post = $this->model->find($postId);
            return $this->respondWithSuccess($post, 'Post created successfully', 201);
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to create post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/posts/{id}
     * Update a post
     */
    public function update($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck)
            return $authCheck;

        if (!$id) {
            return $this->respondWithError('Post ID is required', 400);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if (empty($data)) {
            return $this->respondWithError('No data provided', 400);
        }

        try {
            $existingPost = $this->model->find($id);
            if (!$existingPost) {
                return $this->respondNotFound('Post not found');
            }

            // Update published_at if status changed to published
            if (
                isset($data['status']) && $data['status'] === 'published' &&
                $existingPost['status'] !== 'published' && !isset($data['published_at'])
            ) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            $updated = $this->model->update($id, $data);

            if (!$updated) {
                $errors = $this->model->errors();
                return $this->respondWithValidationError($errors);
            }

            $post = $this->model->find($id);
            return $this->respondWithSuccess($post, 'Post updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/posts/{id}
     * Delete a post
     */
    public function delete($id = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck)
            return $authCheck;

        if (!$id) {
            return $this->respondWithError('Post ID is required', 400);
        }

        try {
            $post = $this->model->find($id);
            if (!$post) {
                return $this->respondNotFound('Post not found');
            }

            $deleted = $this->model->delete($id);

            if (!$deleted) {
                return $this->respondWithError('Failed to delete post', 500);
            }

            return $this->respondWithSuccess(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to delete post: ' . $e->getMessage(), 500);
        }
    }
}