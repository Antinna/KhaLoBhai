<?php

namespace App\Controllers;

use App\Libraries\FirebaseService;

class Firebase extends BaseController
{
    protected FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Display Firebase configuration status
     */
    public function index()
    {
        $data = [
            'title' => 'Firebase Configuration',
            'webConfig' => $this->firebaseService->getWebConfig(),
            'configScript' => $this->firebaseService->getWebConfigScript(),
            'validationErrors' => $this->firebaseService->validateConfig(),
        ];

        return view('firebase/index', $data);
    }

    /**
     * Get Firebase web configuration as JSON (for AJAX requests)
     */
    public function config()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $this->firebaseService->getWebConfig()
        ]);
    }

    /**
     * Test Firebase connection
     */
    public function test()
    {
        $errors = $this->firebaseService->validateConfig();
        
        if (empty($errors)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Firebase configuration is valid'
            ]);
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Firebase configuration has errors',
            'errors' => $errors
        ]);
    }
}