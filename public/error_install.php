<!DOCTYPE html>
<html>
<head>
    <title>Installation Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .error-box {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #e74c3c; }
        .error-type {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Installation Error</h1>
        
        <?php if (isset($GLOBALS["error_type"])): ?>
            <?php if ($GLOBALS["error_type"] == "env-missing"): ?>
                <div class="error-type">
                    <h2>.env File Missing</h2>
                    <p>The <code>.env</code> file is required but was not found.</p>
                    <p><strong>Solution:</strong></p>
                    <ol>
                        <li>Copy <code>.env.example</code> to <code>.env</code></li>
                        <li>Configure your database and app settings</li>
                        <li>Run <code>php artisan key:generate</code></li>
                    </ol>
                </div>
            <?php elseif ($GLOBALS["error_type"] == "php-version"): ?>
                <div class="error-type">
                    <h2>PHP Version Error</h2>
                    <p>Your PHP version is too old. This application requires PHP 8.2.0 or higher.</p>
                    <p><strong>Current Version:</strong> <?php echo PHP_VERSION; ?></p>
                    <p><strong>Required:</strong> PHP 8.2.0+</p>
                </div>
            <?php else: ?>
                <div class="error-type">
                    <h2>Unknown Error</h2>
                    <p>An unknown error occurred during installation.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="error-type">
                <h2>Configuration Error</h2>
                <p>There is a configuration issue with your application.</p>
                <p>Please check your <code>.env</code> file and ensure all required settings are configured.</p>
            </div>
        <?php endif; ?>
        
        <hr>
        <p><small>If you need help, please check the documentation or contact support.</small></p>
    </div>
</body>
</html>

