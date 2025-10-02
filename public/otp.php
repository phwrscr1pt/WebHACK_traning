<?php
session_start();
loadEnv(__DIR__ . '/../.env');

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

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
        $error = 'Invalid OTP code';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔢 OTP Verification</h1>
        
        <?php if ($success): ?>
        <div class="flag-box">
            <h2>Flag #2 Obtained!</h2>
            <code><?php echo htmlspecialchars($_ENV['FLAG2'] ?? 'LOCTH{otp_bypassed}'); ?></code>
            <p>OTP verification successful!</p>
            <a href="flow.php" class="btn">Continue to Stage 3</a>
        </div>
        <?php else: ?>
        
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <p>Please enter your OTP code to continue.</p>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="otp-form">
            <div class="form-group">
                <label>OTP Code (4 digits):</label>
                <input type="text" name="otp" maxlength="4" pattern="[0-9]{4}" required>
            </div>
            <button type="submit" class="btn">Verify</button>
        </form>
        
        <div class="hint-box">
            <strong>Hint:</strong> The OTP is a 4-digit code between 0000-0200. Use Burp Intruder to brute-force it!
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
