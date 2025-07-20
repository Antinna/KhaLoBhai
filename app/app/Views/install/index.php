<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeIgniter 4 API Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .status-card {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 5px solid;
        }

        .status-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .status-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .steps {
            margin-top: 20px;
        }

        .step {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            font-size: 14px;
        }

        .step-success .step-icon {
            background: #28a745;
            color: white;
        }

        .step-error .step-icon {
            background: #dc3545;
            color: white;
        }

        .step-running .step-icon {
            background: #007bff;
            color: white;
        }

        .step-skipped .step-icon {
            background: #6c757d;
            color: white;
        }

        .step-info .step-icon {
            background: #17a2b8;
            color: white;
        }

        .step-content {
            flex: 1;
        }

        .step-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .step-message {
            color: #6c757d;
            font-size: 0.9em;
        }

        .api-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .api-info h3 {
            color: #495057;
            margin-bottom: 15px;
        }

        .endpoint {
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 3px solid #007bff;
        }

        .endpoint code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }

        .refresh-btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        .refresh-btn:hover {
            background: #0056b3;
        }

        .config-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }

        .config-info h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .config-info pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🚀 API Installation</h1>
            <p>CodeIgniter 4 RESTful API Setup</p>
        </div>

        <div class="content">
            <!-- Main Status -->
            <div class="status-card status-<?= $status ?>">
                <h2>
                    <?php if ($status === 'success'): ?>
                        ✅ Installation Successful
                    <?php elseif ($status === 'error'): ?>
                        ❌ Installation Failed
                    <?php else: ?>
                        ⚠️ Installation Warning
                    <?php endif; ?>
                </h2>
                <p><?= esc($message) ?></p>
            </div>

            <!-- Installation Steps -->
            <?php if (!empty($steps)): ?>
                <div class="steps">
                    <h3>Installation Steps:</h3>
                    <?php foreach ($steps as $step): ?>
                        <div class="step step-<?= $step['status'] ?>">
                            <div class="step-icon">
                                <?php
                                switch ($step['status']) {
                                    case 'success':
                                        echo '✓';
                                        break;
                                    case 'error':
                                        echo '✗';
                                        break;
                                    case 'running':
                                        echo '⟳';
                                        break;
                                    case 'skipped':
                                        echo '⊘';
                                        break;
                                    case 'info':
                                        echo 'ℹ';
                                        break;
                                    default:
                                        echo '•';
                                        break;
                                }
                                ?>
                            </div>
                            <div class="step-content">
                                <div class="step-title"><?= esc($step['step']) ?></div>
                                <div class="step-message"><?= esc($step['message']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- API Information (show only on success) -->
            <?php if ($status === 'success'): ?>
                <div class="api-info">
                    <h3>🎉 Your API is Ready!</h3>
                    <p>Your CodeIgniter 4 API has been successfully installed and configured. Here are some endpoints you
                        can try:</p>

                    <div class="endpoint">
                        <strong>API Health Check:</strong><br>
                        <code>GET <?= base_url('api') ?></code>
                    </div>

                    <div class="endpoint">
                        <strong>Users API:</strong><br>
                        <code>GET <?= base_url('api/users') ?></code>
                    </div>

                    <div class="endpoint">
                        <strong>Posts API:</strong><br>
                        <code>GET <?= base_url('api/posts') ?></code>
                    </div>

                    <div class="config-info">
                        <h4>🔑 Authentication Required</h4>
                        <p>All API endpoints require an API key. Include this header in your requests:</p>
                        <pre>X-API-Key: your-secret-api-key-here</pre>
                        <p><small>You can change your API key in the <code>.env</code> file.</small></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Configuration Help (show on error) -->
            <?php if ($status === 'error'): ?>
                <div class="config-info">
                    <h4>🔧 Configuration Help</h4>
                    <p>Make sure your database configuration is correct in your <code>.env</code> file:</p>
                    <pre>DB_HOST = localhost
    DB_PORT = 3306
    DB_NAME = your_database_name
    DB_USERNAME = your_username
    DB_PASSWORD = your_password</pre>
                    <p><small>Create the database if it doesn't exist, then refresh this page.</small></p>
                </div>
            <?php endif; ?>

            <!-- Refresh Button -->
            <button class="refresh-btn" onclick="window.location.reload()">
                🔄 Refresh Installation
            </button>

            <!-- Links -->
            <div style="margin-top: 30px; text-align: center; color: #6c757d;">
                <p>
                    <a href="<?= base_url() ?>" style="color: #007bff; text-decoration: none;">← Back to Home</a> |
                    <a href="<?= base_url('firebase') ?>" style="color: #007bff; text-decoration: none;">Firebase
                        Config</a> |
                    <a href="<?= base_url('api') ?>" style="color: #007bff; text-decoration: none;">API
                        Documentation</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>