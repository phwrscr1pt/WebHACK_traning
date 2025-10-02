<?php
session_start();

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
    
    // Basic input validation (intentionally weak for Boolean SQLi)
    if (strlen($username) > 100 || strlen($password) > 100) {
        $error = 'Input too long';
    } else {
        // Connect to database
        $mysqli = new mysqli(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_USER'] ?? 'locth_user',
            $_ENV['DB_PASS'] ?? 'L0cTh_S3cur3_P@ss',
            $_ENV['DB_NAME'] ?? 'locth_lab'
        );
        
        if ($mysqli->connect_error) {
            $error = 'Database connection failed';
        } else {
            // Vulnerable query - Boolean-based SQLi
            $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password_clear = '$password'";
            $result = $mysqli->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // For Stage 2: Only allow staff role
                if ($user['role'] === 'staff') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: otp.php');
                    exit;
                } elseif ($user['role'] === 'curator') {
                    // For Stage 3: After UNION SQLi dump
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 3);
                    
                    $flag = $_ENV['FLAG3'] ?? 'LOCTH{union_dump_success}';
                    $success = true;
                } else {
                    $error = 'Invalid user role for this stage';
                }
            } else {
                $error = 'Invalid credentials';
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔐 Login Portal</h1>
        
        <?php if ($success): ?>
        <div class="flag-box">
            <h2>Flag #3 Obtained!</h2>
            <code><?php echo htmlspecialchars($flag); ?></code>
            <p>You've successfully logged in as <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="curator.php" class="btn">Continue to Stage 4</a>
        </div>
        <?php else: ?>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="hint-box">
            <strong>Stage 2 Hint:</strong> Try Boolean-based SQL injection to login as 'staff' role.<br>
            <strong>Stage 3 Hint:</strong> Use UNION SQLi from search.php to dump credentials first.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
