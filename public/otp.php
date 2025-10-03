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

// Check if user is logged in as staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';
    $correct_otp = $_ENV['OTP_CODE'] ?? '0137';
    
    if ($otp === $correct_otp) {
        $_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 2);
        $success = true;
    } else {
        $error = 'Invalid OTP';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP - LOCTH Lab</title>
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
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2em;
        }
        
        .user-info {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }
        
        .flag-box {
            background: #d4edda;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .flag-box h2 {
            color: #28a745;
            margin-bottom: 15px;
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
        }
        
        .otp-input {
            width: 100%;
            padding: 20px;
            font-size: 2em;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 15px;
            margin: 20px 0;
            letter-spacing: 10px;
            font-weight: bold;
            color: #333;
            transition: border-color 0.3s;
        }
        
        .otp-input:focus {
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
        <h1>OTP Verification</h1>
        
        <?php if ($success): ?>
        <div class="flag-box">
            <h2>Flag #2</h2>
            <code><?php echo htmlspecialchars($_ENV['FLAG2'] ?? 'LOCTH{otp_bypassed}'); ?></code>
            <p style="margin-top: 15px; color: #666;">Stage 2 Complete!</p>
            <a href="flow.php" class="btn" style="display: inline-block; text-decoration: none; margin-top: 15px;">Continue</a>
        </div>
        <?php else: ?>
        
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" 
                   name="otp" 
                   class="otp-input"
                   maxlength="4" 
                   pattern="[0-9]{4}" 
                   placeholder="0000"
                   required
                   autofocus>
            <button type="submit" class="btn">Verify</button>
        </form>
        
        <div class="hint">
            OTP: 4-digit code (0000-0200)<br>
            Use Burp Intruder to brute force
        </div>
        <?php endif; ?>
    </div>
</body>
</html>