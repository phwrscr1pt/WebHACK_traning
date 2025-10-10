<?php
session_start();

// Initialize completed_stages array
if (!isset($_SESSION['completed_stages'])) {
    $_SESSION['completed_stages'] = array();
}

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}
loadEnv(__DIR__ . '/../.env');

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Input validation
    $username = trim($username);
    $password = trim($password);
    
    // Check for empty fields
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    }
    // Check length limits
    elseif (strlen($username) > 50 || strlen($password) > 50) {
        $error = 'Username and password must be less than 50 characters';
    }
    // Block obvious SQL injection patterns (weak filter - still bypassable)
    elseif (preg_match('/(\bUNION\b|\bSELECT\b.*\bFROM\b|\bDROP\b|\bDELETE\b|\bINSERT\b|\bUPDATE\b)/i', $username . $password)) {
        $error = 'Invalid characters detected';
    }
    // Check for suspicious characters (but allow quotes and dashes for SQLi)
    elseif (preg_match('/[<>{}[\]\\\\]/', $username . $password)) {
        $error = 'Invalid characters in input';
    }
    else {
        // Database connection
        $mysqli = new mysqli(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_USER'] ?? 'locth_user',
            $_ENV['DB_PASS'] ?? 'L0cTh_S3cur3_P@ss',
            $_ENV['DB_NAME'] ?? 'locth_lab'
        );
        
        if ($mysqli->connect_error) {
            $error = 'Database connection failed';
        } else {
            // Still vulnerable - allows Boolean-based SQLi
            $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password_clear = '$password'";
            
            // Suppress warnings and handle errors gracefully
            $result = @$mysqli->query($query);
            
            // Check for SQL errors
            if ($result === false) {
                $error = 'Invalid username or password';
            }
            elseif ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Role-based access control
                if ($user['role'] === 'staff') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['completed_stages'][2] = true;
                    header('Location: otp.php');
                    exit;
                } 
                elseif ($user['role'] === 'curator') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['completed_stages'][3] = true;
                    $flag = $_ENV['FLAG3'] ?? 'LOCTH{union_dump_success}';
                    $success = true;
                } 
                else {
                    $error = 'Access denied for this user role';
                }
            } else {
                $error = 'Invalid username or password';
            }
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - LOCTH Lab</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 2em;
        }
        
        .flag-box {
            background: #d4edda;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .flag-box code {
            font-size: 1.3em;
            color: #28a745;
            font-weight: bold;
            display: block;
            margin: 15px 0;
        }
        
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .hint {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php if ($success): ?>
        <div class="flag-box">
            <h2>Flag #3</h2>
            <code><?php echo htmlspecialchars($flag); ?></code>
            <a href="curator.php" class="btn" style="display: inline-block; text-decoration: none; margin-top: 15px;">Continue</a>
        </div>
        <?php else: ?>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required maxlength="50">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required maxlength="50">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="hint">
            Stage 2: Boolean SQLi → login as 'staff'<br>
            Stage 3: UNION SQLi → get credentials first
        </div>
        <?php endif; ?>
    </div>
</body>
</html>