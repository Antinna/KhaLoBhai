<?php

namespace App\Libraries;

use Config\Firebase;

class FirebaseService
{
    protected Firebase $config;

    public function __construct()
    {
        $this->config = config('Firebase');
    }

    /**
     * Get Firebase Web Configuration for JavaScript
     */
    public function getWebConfig(): array
    {
        return $this->config->web;
    }

    /**
     * Get Firebase Service Account Configuration
     */
    public function getServiceAccountConfig(): array
    {
        return $this->config->serviceAccount;
    }

    /**
     * Generate Firebase Web Config JavaScript
     */
    public function getWebConfigScript(): string
    {
        $config = $this->getWebConfig();
        
        return "
        // Firebase configuration
        const firebaseConfig = " . json_encode($config, JSON_PRETTY_PRINT) . ";
        
        // Initialize Firebase
        // import { initializeApp } from 'firebase/app';
        // const app = initializeApp(firebaseConfig);
        ";
    }

    /**
     * Get Firebase Database URL
     */
    public function getDatabaseURL(): string
    {
        return $this->config->databaseURL;
    }

    /**
     * Get Firebase Storage Bucket
     */
    public function getStorageBucket(): string
    {
        return $this->config->storageBucket;
    }

    /**
     * Validate Firebase Configuration
     */
    public function validateConfig(): array
    {
        $errors = [];
        
        // Check web config
        if (empty($this->config->web['apiKey'])) {
            $errors[] = 'Firebase API Key is missing';
        }
        
        if (empty($this->config->web['projectId'])) {
            $errors[] = 'Firebase Project ID is missing';
        }
        
        // Check service account config
        if (empty($this->config->serviceAccount['private_key'])) {
            $errors[] = 'Firebase Private Key is missing';
        }
        
        if (empty($this->config->serviceAccount['client_email'])) {
            $errors[] = 'Firebase Client Email is missing';
        }
        
        return $errors;
    }
}