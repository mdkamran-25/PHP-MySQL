<?php
/**
 * Quick Setup Script for Free Hosting
 * Run this once to configure your database connection
 */

// Check if this is the first run
if ($_GET['setup'] !== 'true') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Product Catalog - First Time Setup</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .container { background: #f9f9f9; padding: 30px; border-radius: 10px; }
            h1 { color: #333; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
            form { margin: 20px 0; }
            input, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #0056b3; }
            .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 Product Catalog - First Time Setup</h1>
            
            <div class="info">
                <strong>Welcome!</strong> This setup will configure your database connection for free hosting.
            </div>

            <div class="step">
                <h3>Step 1: Choose Your Hosting Platform</h3>
                <form method="POST">
                    <label>Select Hosting Platform:</label>
                    <select name="hosting_platform" required>
                        <option value="">Choose Platform...</option>
                        <option value="infinityfree">InfinityFree (Recommended - 100% Free)</option>
                        <option value="000webhost">000webhost (Free with 1h downtime)</option>
                        <option value="railway">Railway (Free $5 credit)</option>
                        <option value="custom">Custom/Other</option>
                    </select>
                </form>
            </div>

            <div class="step">
                <h3>Step 2: Enter Database Details</h3>
                <p><strong>Get these from your hosting provider's control panel:</strong></p>
                
                <form method="POST" action="?setup=true">
                    <input type="hidden" name="hosting_platform" value="" id="platform_hidden">
                    
                    <label>Database Host:</label>
                    <input type="text" name="db_host" placeholder="e.g., sql200.infinityfree.com" value="sql200.infinityfree.com" required>
                    
                    <label>Database Name:</label>
                    <input type="text" name="db_name" placeholder="e.g., if0_39899884_productcatalog" value="if0_39899884_productcatalog" required>
                    
                    <label>Database Username:</label>
                    <input type="text" name="db_user" placeholder="e.g., if0_39899884" value="if0_39899884" required>
                    
                    <label>Database Password:</label>
                    <input type="password" name="db_pass" placeholder="Your database password" required>
                    
                    <button type="submit">💾 Save Configuration & Test Connection</button>
                </form>
            </div>

            <div class="step">
                <h3>Step 3: After Configuration</h3>
                <ol>
                    <li>Import your database using phpMyAdmin</li>
                    <li>Upload the file: <code>sql/product_catalog_dump.sql</code></li>
                    <li>Your app will be ready with 73 products!</li>
                </ol>
            </div>
        </div>

        <script>
            document.querySelector('select[name="hosting_platform"]').addEventListener('change', function() {
                document.getElementById('platform_hidden').value = this.value;
                
                // Auto-fill common values based on platform
                const host = document.querySelector('input[name="db_host"]');
                switch(this.value) {
                    case 'infinityfree':
                        host.placeholder = 'sql200.infinityfree.com (or similar)';
                        break;
                    case '000webhost':
                        host.placeholder = 'localhost';
                        break;
                    case 'railway':
                        host.placeholder = 'Will be provided by Railway';
                        break;
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Process setup
if ($_POST) {
    $host = $_POST['db_host'];
    $name = $_POST['db_name'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    
    // Test connection
    try {
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $success = true;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        $success = false;
    }
    
    if ($success) {
        // Update database config file
        $config = file_get_contents('config/database_free.php');
        
        // Replace placeholders
        $config = str_replace('if0_12345678_productcatalog', $name, $config);
        $config = str_replace('if0_12345678', $user, $config);
        $config = str_replace('your_password', $pass, $config);
        $config = str_replace('sql200.infinityfree.com', $host, $config);
        
        // Backup original and save new config
        if (!file_exists('config/database_original.php')) {
            copy('config/database.php', 'config/database_original.php');
        }
        file_put_contents('config/database.php', $config);
        
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setup Complete</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
                .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; text-align: center; }
                .next-steps { background: #fff3cd; color: #856404; padding: 20px; border-radius: 5px; margin: 20px 0; }
                a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='success'>
                <h2>✅ Database Connection Successful!</h2>
                <p>Your configuration has been saved.</p>
            </div>
            
            <div class='next-steps'>
                <h3>📋 Next Steps:</h3>
                <ol>
                    <li><strong>Import Database:</strong> Go to phpMyAdmin and import <code>sql/product_catalog_dump.sql</code></li>
                    <li><strong>Delete Setup:</strong> Remove this <code>setup.php</code> file for security</li>
                    <li><strong>Test App:</strong> Visit your homepage to see 73 products!</li>
                </ol>
            </div>
            
            <div style='text-align: center;'>
                <a href='index.php'>🏠 Go to Homepage</a>
                <a href='?delete_setup=true' onclick='return confirm(\"Delete setup file?\")'>🗑️ Delete Setup File</a>
            </div>
        </body>
        </html>";
    } else {
        echo "
        <div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px auto; max-width: 800px;'>
            <h3>❌ Connection Failed</h3>
            <p><strong>Error:</strong> $error</p>
            <p><a href='javascript:history.back()'>← Go Back and Try Again</a></p>
        </div>";
    }
    exit;
}

// Delete setup file
if ($_GET['delete_setup'] === 'true') {
    unlink(__FILE__);
    header('Location: index.php');
    exit;
}
?>