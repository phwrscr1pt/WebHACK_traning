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

// Check QA mode (set by Stage 4)
if (!isset($_SESSION['qa']) || $_SESSION['qa'] !== 1) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Upload - LOCTH Lab</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
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
                text-align: center;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h1 { color: #dc3545; margin-bottom: 20px; }
            p { color: #666; font-size: 1.1em; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Access Denied</h1>
            <p>QA Mode not enabled. Complete Stage 4 first.</p>
        </div>
    </body>
    </html>
    ');
}

$message = '';
$uploaded_file = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $upload_dir = __DIR__ . '/uploads/';
    
    // Layer 1: Content-Type check (weak - trusts client)
    $content_type = $file['type'];
    if ($content_type !== 'image/png' && $content_type !== 'image/jpeg') {
        $message = 'Error: Only PNG and JPEG images allowed';
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
                $parts = explode('.', $filename);
                if (count($parts) > 2) {
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
            
            <?php if ($uploaded_file): ?>
                <div class="success-box">
                    <strong>Next:</strong> Use runner.php to execute<br>
                    <code>runner.php?f=<?php echo urlencode($uploaded_file); ?>&cmd=id</code>
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
        
        <div class="hint-box">
            <h3>Hints</h3>
            <p>1. Content-Type image/png</p>
            <p>2. Double extension</p>
            <p>3. PNG magic bytes</code></p>
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