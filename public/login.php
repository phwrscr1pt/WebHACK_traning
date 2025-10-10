<?php
// Suppress errors completely
@ini_set('display_errors', 0);
@error_reporting(0);

session_start();

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return;
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}
loadEnv(__DIR__ . '/../.env');

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // INPUT VALIDATION: Remove all numbers (0-9)
        $username = preg_replace('/[0-9]/', '', $username);
        $password = preg_replace('/[0-9]/', '', $password);
        
        // Trim whitespace
        $username = trim($username);
        $password = trim($password);
        
        // Check empty after filtering
        if (empty($username) || empty($password)) {
            $error = 'Username and password are required (numbers not allowed)';
        }
        elseif (strlen($username) > 200 || strlen($password) > 200) {
            $error = 'Input too long';
        }
        else {
            // Database connection
            $mysqli = @mysqli_connect(
                isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost',
                isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'locth_user',
                isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : 'L0cTh_S3cur3_P@ss',
                isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'locth_lab'
            );
            
            if (!$mysqli) {
                $error = 'Database connection failed';
            } else {
                // VULNERABLE - No input sanitization (except number removal)
                // This still allows: ' or 'a'='a'#, ' or role='staff'#, etc.
                $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password_clear = '$password'";
                
                // Execute query
                $result = @mysqli_query($mysqli, $query);
                
                if ($result === false || $result === null) {
                    // Query failed due to SQL error
                    $error = 'Invalid username or password';
                }
                elseif (@mysqli_num_rows($result) > 0) {
                    // Login successful - fetch first user
                    $user = @mysqli_fetch_assoc($result);
                    
                    if ($user && isset($user['role'])) {
                        // Check role
                        if ($user['role'] === 'staff') {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['login_time'] = time();
                            
                            @mysqli_free_result($result);
                            @mysqli_close($mysqli);
                            
                            header('Location: otp.php');
                            exit;
                        } 
                        elseif ($user['role'] === 'curator') {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['login_time'] = time();
                            $_SESSION['progress'] = max(isset($_SESSION['progress']) ? $_SESSION['progress'] : 0, 3);
                            
                            $flag = isset($_ENV['FLAG3']) ? $_ENV['FLAG3'] : 'LOCTH{union_dump_success}';
                            $success = true;
                        }
                        elseif ($user['role'] === 'admin') {
                            // If admin is returned, try to get staff instead
                            @mysqli_data_seek($result, 0);
                            $found_staff = false;
                            while ($row = @mysqli_fetch_assoc($result)) {
                                if ($row['role'] === 'staff') {
                                    $_SESSION['user_id'] = $row['id'];
                                    $_SESSION['username'] = $row['username'];
                                    $_SESSION['role'] = $row['role'];
                                    $_SESSION['login_time'] = time();
                                    
                                    @mysqli_free_result($result);
                                    @mysqli_close($mysqli);
                                    
                                    header('Location: otp.php');
                                    exit;
                                }
                            }
                            
                            if (!$found_staff) {
                                $error = 'Access denied for this user role';
                            }
                        }
                        else {
                            $error = 'Access denied for this user role';
                        }
                    } else {
                        $error = 'Invalid username or password';
                    }
                    
                    @mysqli_free_result($result);
                } 
                else {
                    $error = 'Invalid username or password';
                    if ($result) {
                        @mysqli_free_result($result);
                    }
                }
                
                @mysqli_close($mysqli);
            }
        }
    } catch (Exception $e) {
        $error = 'Invalid username or password';
    } catch (Error $e) {
        $error = 'Invalid username or password';
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
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
