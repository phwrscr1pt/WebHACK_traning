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

// Auto-enable QA mode for free play (no Stage 4 requirement)
if (!isset($_SESSION['qa'])) {
    $_SESSION['qa'] = 1;
}

$message = '';
$uploaded_file = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $upload_dir = __DIR__ . '/uploads/';
    
    // Create uploads directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Layer 1: Content-Type check (weak - trusts client)
    $content_type = $file['type'];
    if ($content_type !== 'image/png' && $content_type !== 'image/jpeg' && $content_type !== 'image/gif') {
        $message = 'Error: Only PNG, JPEG and GIF images allowed';
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
                // VULNERABILITY: Double extension bug
                // If filename is shell.php.png, it will save as shell.php
                $parts = explode('.', $filename);
                
                if (count($parts) >= 3) {
                    // BUG: Takes the SECOND extension instead of last
                    // shell.php.png -> saves as shell.php
                    $base_name = $parts[0];
                    $first_ext = $parts[1];
                    $final_name = $base_name . '.' . $first_ext;
                } else {
                    // Normal case: test.png -> test.png
                    $final_name = $filename;
                }
                
                $target_path = $upload_dir . $final_name;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $uploaded_file = $final_name;
                    $message = "Success! File uploaded as: $final_name";
                    
                    // Check if PHP file was uploaded
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
    <title>Upload - LOCTH Lab</title>
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
            padding: 40px 20px;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 40px;
        }
        
        .upload-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        
        .upload-icon {
            font-size: 4em;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .file-label {
            color: #666;
            font-size: 1.1em;
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
            margin-top: 20px;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .hint-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .hint-box h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .hint-box code {
            background: #f8f9fa;
            padding: 3px 8px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: monospace;
            font-size: 0.9em;
        }
        
        .hint-box p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0;
        }
        
        .selected-file {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #555;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            font-size: 1.1em;
            transition: opacity 0.3s;
        }
        
        .back-link a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>File Upload</h1>
        
        <div class="upload-box">
            <?php if ($message): ?>
                <div class="<?php echo strpos($message, 'Success') !== false ? 'success-box' : 'error-box'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($uploaded_file && strpos($uploaded_file, '.php') !== false): ?>
                <div class="success-box">
                    <strong>Next Step:</strong> Use runner.php to execute<br>
                    <code>runner.php?f=<?php echo urlencode($uploaded_file); ?>&cmd=id</code><br><br>
                    <strong>Get Flag:</strong><br>
                    <code>runner.php?f=<?php echo urlencode($uploaded_file); ?>&cmd=cat%20../flags/final_flag.txt</code>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <div class="upload-icon">📁</div>
                    <div class="file-label">Click to select file</div>
                    <div class="selected-file" id="fileName" style="display: none;"></div>
                </div>
                <input type="file" name="file" id="fileInput" required onchange="showFileName()">
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>
        
<!--        <div class="hint-box">
            <h3>Stage 5: File Upload Bypass</h3>
            <p><strong>Vulnerability:</strong> Double extension bug</p>
            <p><strong>Exploit:</strong> filename.php.png → saves as filename.php</p>
            <p><strong>Bypass Filters:</strong></p>
            <p>1. Content-Type: <code>image/png</code></p>
            <p>2. Extension: <code>.png</code> (last extension)</p>
            <p>3. Magic bytes: Start with PNG header <code>\x89PNG</code></p>
            <p style="margin-top: 15px;"><strong>Example Payload:</strong></p>
            <p>Create file: <code>shell.php.png</code> with content:</p>
            <p><code>&lt;?php system($_GET['cmd']); ?&gt;</code></p>
            <p>Add PNG magic bytes at the start of file</p>
        </div> -->
        
        <div class="back-link">
            <a href="flow.php">← Back to Challenge Flow</a>
        </div>
    </div>
    
    <script>
        function showFileName() {
            const input = document.getElementById('fileInput');
            const display = document.getElementById('fileName');
            if (input.files.length > 0) {
                display.textContent = 'Selected: ' + input.files[0].name;
                display.style.display = 'block';
            }
        }
    </script>
</body>
</html>
