<?php
// Start session first
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if config exists
$config_path = '../config.php';
if (!file_exists($config_path)) {
    die("Error: config.php not found at: " . realpath('..') . "/config.php");
}

require_once $config_path;

// Check if $pdo exists
if (!isset($pdo)) {
    die("Error: Database connection failed. Check config.php");
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Debug: Check if form data is received
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            // Debug: Check if user exists
            if (!$admin) {
                $error = "User not found in database!";
            } else {
                // Check password - try both hashed and plain text
                $password_valid = false;
                
                // Try password_verify first (for hashed passwords)
                if (password_verify($password, $admin['password'])) {
                    $password_valid = true;
                }
                // Fallback to plain text comparison (for testing only)
                elseif ($password === $admin['password']) {
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password! (Debug: password mismatch)";
                }
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: rgba(10, 10, 10, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        label {
            display: block;
            color: #a0a0a0;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        input {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        button:hover { transform: translateY(-2px); }
        .error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .debug-info {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-lock"></i> Admin Login</h2>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Back to Portfolio</a>
        </div>
        
        <!-- Debug info (remove in production) -->
        <div class="debug-info">
            Config path: <?php echo realpath($config_path) ?: 'Not found'; ?><br>
            Session status: <?php echo session_status(); ?>
        </div>
    </div>
</body>
</html>