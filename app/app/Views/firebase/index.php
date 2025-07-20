<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .config-section {
            background: #f5f5f5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .success {
            color: #388e3c;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        pre {
            background: #263238;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }

        .btn {
            background: #1976d2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .btn:hover {
            background: #1565c0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= $title ?></h1>

        <?php if (!empty($validationErrors)): ?>
            <div class="error">
                <h3>Configuration Errors:</h3>
                <ul>
                    <?php foreach ($validationErrors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Note:</strong> Update your .env file with your Firebase credentials to resolve these errors.</p>
            </div>
        <?php else: ?>
            <div class="success">
                <h3>✓ Firebase Configuration is Valid</h3>
                <p>Your Firebase configuration is properly set up!</p>
            </div>
        <?php endif; ?>

        <div class="config-section">
            <h2>Firebase Web Configuration</h2>
            <p>This configuration is used for client-side Firebase initialization:</p>
            <pre><?= esc(json_encode($webConfig, JSON_PRETTY_PRINT)) ?></pre>
        </div>

        <div class="config-section">
            <h2>JavaScript Configuration</h2>
            <p>Use this script in your frontend to initialize Firebase:</p>
            <pre><?= esc($configScript) ?></pre>
        </div>

        <div class="config-section">
            <h2>Usage Examples</h2>
            <h3>In your JavaScript files:</h3>
            <pre>
// Import Firebase modules
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';
import { getFirestore } from 'firebase/firestore';
import { getStorage } from 'firebase/storage';

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase services
const auth = getAuth(app);
const db = getFirestore(app);
const storage = getStorage(app);
            </pre>

            <h3>In your CodeIgniter Controllers:</h3>
            <pre>
// Load Firebase service
$firebaseService = new \App\Libraries\FirebaseService();

// Get web config for frontend
$webConfig = $firebaseService->getWebConfig();

// Get service account config for server-side operations
$serviceAccount = $firebaseService->getServiceAccountConfig();
            </pre>
        </div>

        <div class="config-section">
            <h2>Actions</h2>
            <button class="btn" onclick="testConnection()">Test Configuration</button>
            <button class="btn" onclick="getConfig()">Get Config JSON</button>
        </div>

        <div id="result"></div>
    </div>

    <script>
        async function testConnection() {
            try {
                const response = await fetch('/firebase/test');
                const data = await response.json();
                showResult(data);
            } catch (error) {
                showResult({ status: 'error', message: 'Failed to test connection' });
            }
        }

        async function getConfig() {
            try {
                const response = await fetch('/firebase/config');
                const data = await response.json();
                showResult(data);
            } catch (error) {
                showResult({ status: 'error', message: 'Failed to get config' });
            }
        }

        function showResult(data) {
            const resultDiv = document.getElementById('result');
            const className = data.status === 'success' ? 'success' : 'error';
            resultDiv.innerHTML = `
                <div class="${className}">
                    <h3>Result:</h3>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
    </script>
</body>

</html>