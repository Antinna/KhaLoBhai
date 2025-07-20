<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'title',
        'content',
        'excerpt',
        'status',
        'user_id',
        'featured_image',
        'published_at',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'content' => 'required|min_length[10]',
        'status' => 'permit_empty|in_list[draft,published,archived]',
        'user_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Title is required',
            'min_length' => 'Title must be at least 3 characters long',
            'max_length' => 'Title cannot exceed 255 characters'
        ],
        'content' => [
            'required' => 'Content is required',
            'min_length' => 'Content must be at least 10 characters long'
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateExcerpt'];
    protected $beforeUpdate = ['generateExcerpt'];

    /**
     * Generate excerpt from content before insert/update
     */
    protected function generateExcerpt(array $data)
    {
        if (isset($data['data']['content']) && empty($data['data']['excerpt'])) {
            $content = strip_tags($data['data']['content']);
            $data['data']['excerpt'] = substr($content, 0, 150) . '...';
        }
        return $data;
    }

    /**
     * Get posts with user information
     */
    public function getPostsWithUser()
    {
        return $this->select('posts.*, users.name as author_name, users.email as author_email')
                    ->join('users', 'users.id = posts.user_id', 'left')
                    ->findAll();
    }

    /**
     * Get published posts only
     */
    public function getPublishedPosts()
    {
        return $this->where('status', 'published')
                    ->where('published_at <=', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Get posts by user
     */
    public function getPostsByUser(int $userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Search posts by title or content
     */
    public function searchPosts(string $query)
    {
        return $this->like('title', $query)
                    ->orLike('content', $query)
                    ->findAll();
    }
}