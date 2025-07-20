<?php

namespace App\Libraries;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class FileUploadService
{
    protected array $allowedTypes = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
        'media' => ['mp4', 'avi', 'mov', 'wmv', 'mp3', 'wav']
    ];

    protected array $maxSizes = [
        'images' => 5 * 1024 * 1024,      // 5MB
        'documents' => 10 * 1024 * 1024,  // 10MB
        'media' => 50 * 1024 * 1024       // 50MB
    ];

    /**
     * Upload file to public directory (publicly accessible)
     */
    public function uploadPublic(UploadedFile $file, string $directory = 'uploads', string $type = 'images'): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'error' => 'Invalid file upload',
                'details' => $file->getErrorString()
            ];
        }

        // Validate file type
        $validation = $this->validateFile($file, $type);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'File validation failed',
                'details' => $validation['error']
            ];
        }

        try {
            // Generate unique filename
            $newName = $this->generateUniqueFilename($file);
            
            // Create upload path
            $uploadPath = FCPATH . $directory . DIRECTORY_SEPARATOR;
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file
            $file->move($uploadPath, $newName);

            return [
                'success' => true,
                'filename' => $newName,
                'original_name' => $file->getClientName(),
                'path' => $directory . '/' . $newName,
                'url' => base_url($directory . '/' . $newName),
                'size' => $file->getSize(),
                'type' => $file->getClientMimeType()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload failed',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload file to writable directory (private, not directly accessible)
     */
    public function uploadPrivate(UploadedFile $file, string $directory = 'uploads', string $type = 'documents'): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'error' => 'Invalid file upload',
                'details' => $file->getErrorString()
            ];
        }

        // Validate file type
        $validation = $this->validateFile($file, $type);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'File validation failed',
                'details' => $validation['error']
            ];
        }

        try {
            // Generate unique filename
            $newName = $this->generateUniqueFilename($file);
            
            // Create upload path in writable directory
            $uploadPath = WRITEPATH . $directory . DIRECTORY_SEPARATOR;
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file
            $file->move($uploadPath, $newName);

            return [
                'success' => true,
                'filename' => $newName,
                'original_name' => $file->getClientName(),
                'path' => $directory . '/' . $newName,
                'size' => $file->getSize(),
                'type' => $file->getClientMimeType(),
                'private' => true
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload failed',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file, string $type): array
    {
        // Check if type is allowed
        if (!isset($this->allowedTypes[$type])) {
            return [
                'valid' => false,
                'error' => 'Invalid file type category'
            ];
        }

        // Check file extension
        $extension = strtolower($file->getClientExtension());
        if (!in_array($extension, $this->allowedTypes[$type])) {
            return [
                'valid' => false,
                'error' => 'File extension not allowed. Allowed: ' . implode(', ', $this->allowedTypes[$type])
            ];
        }

        // Check file size
        if ($file->getSize() > $this->maxSizes[$type]) {
            $maxSizeMB = $this->maxSizes[$type] / (1024 * 1024);
            return [
                'valid' => false,
                'error' => "File too large. Maximum size: {$maxSizeMB}MB"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientExtension();
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(string $filePath, bool $isPrivate = false): bool
    {
        try {
            $fullPath = $isPrivate 
                ? WRITEPATH . $filePath 
                : FCPATH . $filePath;

            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $filePath, bool $isPrivate = false): ?array
    {
        try {
            $fullPath = $isPrivate 
                ? WRITEPATH . $filePath 
                : FCPATH . $filePath;

            if (!file_exists($fullPath)) {
                return null;
            }

            $file = new File($fullPath);
            
            return [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                'path' => $filePath,
                'url' => $isPrivate ? null : base_url($filePath)
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Serve private file (for download)
     */
    public function servePrivateFile(string $filePath): \CodeIgniter\HTTP\ResponseInterface
    {
        $fullPath = WRITEPATH . $filePath;
        
        if (!file_exists($fullPath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        $file = new File($fullPath);
        
        return response()->download($fullPath, null)->setFileName($file->getFilename());
    }
}