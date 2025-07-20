<?php

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Libraries\FileUploadService;

class FilesController extends BaseApiController
{
    protected FileUploadService $fileService;

    public function __construct()
    {
        parent::__construct();
        $this->fileService = new FileUploadService();
    }

    /**
     * POST /api/files/upload
     * Upload a file (public)
     */
    public function upload()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $file = $this->request->getFile('file');
        if (!$file) {
            return $this->respondWithError('No file provided', 400);
        }

        $type = $this->request->getPost('type') ?? 'images';
        $directory = $this->request->getPost('directory') ?? 'uploads';

        try {
            $result = $this->fileService->uploadPublic($file, $directory, $type);

            if ($result['success']) {
                return $this->respondWithSuccess($result, 'File uploaded successfully', 201);
            } else {
                return $this->respondWithError($result['error'], 400, $result['details'] ?? null);
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/files/upload-private
     * Upload a private file
     */
    public function uploadPrivate()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $file = $this->request->getFile('file');
        if (!$file) {
            return $this->respondWithError('No file provided', 400);
        }

        $type = $this->request->getPost('type') ?? 'documents';
        $directory = $this->request->getPost('directory') ?? 'uploads/private';

        try {
            $result = $this->fileService->uploadPrivate($file, $directory, $type);

            if ($result['success']) {
                return $this->respondWithSuccess($result, 'Private file uploaded successfully', 201);
            } else {
                return $this->respondWithError($result['error'], 400, $result['details'] ?? null);
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/files/upload-profile
     * Upload profile image
     */
    public function uploadProfile()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $file = $this->request->getFile('profile_image');
        if (!$file) {
            return $this->respondWithError('No profile image provided', 400);
        }

        try {
            $result = $this->fileService->uploadPublic($file, 'uploads/profiles', 'images');

            if ($result['success']) {
                // You could save the file path to user's profile in database here
                return $this->respondWithSuccess($result, 'Profile image uploaded successfully', 201);
            } else {
                return $this->respondWithError($result['error'], 400, $result['details'] ?? null);
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/info/{path}
     * Get file information
     */
    public function info($path = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        if (!$path) {
            return $this->respondWithError('File path is required', 400);
        }

        // Decode the path (in case it's URL encoded)
        $path = urldecode($path);
        $isPrivate = $this->request->getGet('private') === 'true';

        try {
            $fileInfo = $this->fileService->getFileInfo($path, $isPrivate);

            if ($fileInfo) {
                return $this->respondWithSuccess($fileInfo, 'File information retrieved');
            } else {
                return $this->respondNotFound('File not found');
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to get file info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/files/remove
     * Delete a file
     */
    public function remove()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if (empty($data['path'])) {
            return $this->respondWithError('File path is required', 400);
        }

        $isPrivate = $data['private'] ?? false;

        try {
            $deleted = $this->fileService->deleteFile($data['path'], $isPrivate);

            if ($deleted) {
                return $this->respondWithSuccess(null, 'File deleted successfully');
            } else {
                return $this->respondWithError('Failed to delete file or file not found', 404);
            }
        } catch (\Exception $e) {
            return $this->respondWithError('Delete failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/files/download/{path}
     * Download a private file
     */
    public function download($path = null)
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        if (!$path) {
            return $this->respondWithError('File path is required', 400);
        }

        // Decode the path
        $path = urldecode($path);

        try {
            return $this->fileService->servePrivateFile($path);
        } catch (\Exception $e) {
            return $this->respondWithError('Download failed: ' . $e->getMessage(), 404);
        }
    }

    /**
     * GET /api/files/list
     * List uploaded files (for admin)
     */
    public function list()
    {
        // Check authentication
        $authCheck = $this->requireAuth();
        if ($authCheck) return $authCheck;

        $directory = $this->request->getGet('directory') ?? 'uploads';
        $isPrivate = $this->request->getGet('private') === 'true';

        try {
            $basePath = $isPrivate ? WRITEPATH : FCPATH;
            $fullPath = $basePath . $directory;

            if (!is_dir($fullPath)) {
                return $this->respondWithError('Directory not found', 404);
            }

            $files = [];
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($basePath, '', $file->getPathname());
                    $relativePath = str_replace('\\', '/', $relativePath);
                    
                    $files[] = [
                        'filename' => $file->getFilename(),
                        'path' => $relativePath,
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'url' => $isPrivate ? null : base_url($relativePath)
                    ];
                }
            }

            return $this->respondWithSuccess([
                'files' => $files,
                'directory' => $directory,
                'private' => $isPrivate,
                'total' => count($files)
            ], 'Files listed successfully');

        } catch (\Exception $e) {
            return $this->respondWithError('Failed to list files: ' . $e->getMessage(), 500);
        }
    }
}