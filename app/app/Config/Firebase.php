<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
    /**
     * Firebase Web Configuration
     */
    public array $web = [
        'apiKey'            => '',
        'authDomain'        => '',
        'projectId'         => '',
        'storageBucket'     => '',
        'messagingSenderId' => '',
        'appId'             => '',
        'measurementId'     => '', // Optional for Google Analytics
    ];

    /**
     * Firebase Service Account Configuration
     */
    public array $serviceAccount = [
        'type'                        => 'service_account',
        'project_id'                  => '',
        'private_key_id'              => '',
        'private_key'                 => '',
        'client_email'                => '',
        'client_id'                   => '',
        'auth_uri'                    => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri'                   => 'https://oauth2.googleapis.com/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url'        => '',
        'universe_domain'             => 'googleapis.com',
    ];

    /**
     * Firebase Database URL (for Realtime Database)
     */
    public string $databaseURL = '';

    /**
     * Firebase Storage Bucket URL
     */
    public string $storageBucket = '';

    public function __construct()
    {
        parent::__construct();

        // Load from environment variables
        $this->web = [
            'apiKey'            => env('FIREBASE_API_KEY', ''),
            'authDomain'        => env('FIREBASE_AUTH_DOMAIN', ''),
            'projectId'         => env('FIREBASE_PROJECT_ID', ''),
            'storageBucket'     => env('FIREBASE_STORAGE_BUCKET', ''),
            'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', ''),
            'appId'             => env('FIREBASE_APP_ID', ''),
            'measurementId'     => env('FIREBASE_MEASUREMENT_ID', ''),
        ];

        $this->serviceAccount = [
            'type'                        => env('FIREBASE_SERVICE_ACCOUNT_TYPE', 'service_account'),
            'project_id'                  => env('FIREBASE_SERVICE_ACCOUNT_PROJECT_ID', env('FIREBASE_PROJECT_ID', '')),
            'private_key_id'              => env('FIREBASE_SERVICE_ACCOUNT_PRIVATE_KEY_ID', ''),
            'private_key'                 => env('FIREBASE_PRIVATE_KEY', ''),
            'client_email'                => env('FIREBASE_CLIENT_EMAIL', ''),
            'client_id'                   => env('FIREBASE_SERVICE_ACCOUNT_CLIENT_ID', ''),
            'auth_uri'                    => env('FIREBASE_SERVICE_ACCOUNT_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth'),
            'token_uri'                   => env('FIREBASE_SERVICE_ACCOUNT_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
            'auth_provider_x509_cert_url' => env('FIREBASE_SERVICE_ACCOUNT_AUTH_PROVIDER_CERT_URL', 'https://www.googleapis.com/oauth2/v1/certs'),
            'client_x509_cert_url'        => env('FIREBASE_SERVICE_ACCOUNT_CLIENT_CERT_URL', ''),
            'universe_domain'             => env('FIREBASE_SERVICE_ACCOUNT_UNIVERSE_DOMAIN', 'googleapis.com'),
        ];

        $this->databaseURL = env('FIREBASE_DATABASE_URL', '');
        $this->storageBucket = env('FIREBASE_STORAGE_BUCKET', '');
    }
}