<?php
session_start();

// Auto-enable QA mode for free play (no Stage 4 requirement)
if (!isset($_SESSION['qa'])) {
    $_SESSION['qa'] = 1;
}

$filename = $_GET['f'] ?? '';
$cmd = $_GET['cmd'] ?? '';

if (empty($filename)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Runner - LOCTH Lab</title>
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
                background: white;
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            
            .info {
                background: #d1ecf1;
                color: #0c5460;
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
            }
            
            code {
                background: #f8f9fa;
                padding: 3px 8px;
                border-radius: 4px;
                color: #e83e8c;
                font-family: monospace;
            }
            
            .example {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 10px 0;
            }
            
            .back-link {
                margin-top: 30px;
                text-align: center;
            }
            
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                transition: all 0.3s;
            }
            
            .btn:hover {
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>PHP File Runner</h1>
            
            <div class="info">
                <strong>Usage:</strong> Execute uploaded PHP files<br>
                <code>runner.php?f=filename.php&cmd=command</code>
            </div>
            
            <h3>Examples:</h3>
            
            <div class="example">
                <strong>Test execution:</strong><br>
                <code>runner.php?f=shell.php&cmd=id</code>
            </div>
            
            <div class="example">
                <strong>List files:</strong><br>
                <code>runner.php?f=shell.php&cmd=ls -la</code>
            </div>
            
            <div class="example">
                <strong>Read flag:</strong><br>
                <code>runner.php?f=shell.php&cmd=cat ../flags/final_flag.txt</code>
            </div>
            
            <div class="example">
                <strong>Check current directory:</strong><br>
                <code>runner.php?f=shell.php&cmd=pwd</code>
            </div>
            
            <div class="back-link">
                <a href="upload.php" class="btn">Back to Upload</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Basic path traversal protection
$filename = basename($filename);

// Only allow .php files
if (substr($filename, -4) !== '.php') {
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - Runner</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-box {
                background: white;
                border-radius: 15px;
                padding: 40px;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            h1 { color: #dc3545; margin-bottom: 15px; }
            p { color: #666; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>Error</h1>
            <p>Only .php files can be executed via runner</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$file_path = __DIR__ . '/uploads/' . $filename;

if (!file_exists($file_path)) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - File Not Found</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-box {
                background: white;
                border-radius: 15px;
                padding: 40px;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            h1 { color: #dc3545; margin-bottom: 15px; }
            p { color: #666; }
            code {
                background: #f8f9fa;
                padding: 3px 8px;
                border-radius: 4px;
                color: #e83e8c;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>File Not Found</h1>
            <p>The file <code><?php echo htmlspecialchars($filename); ?></code> does not exist in uploads directory</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Pass cmd parameter to the uploaded PHP file
$_GET['cmd'] = $cmd;

// Execute the PHP file
?>
<!DOCTYPE html>
<html>
<head>
    <title>Runner Output - LOCTH Lab</title>
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
            max-width: 900px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .execution-info {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .execution-info code {
            background: #f8f9fa;
            padding: 3px 8px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: monospace;
        }
        
        .output-box {
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            overflow-x: auto;
        }
        
        .output-box pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .success-note {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .back-link {
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Command Execution Output</h1>
        
        <div class="execution-info">
            <strong>Executing:</strong> <code><?php echo htmlspecialchars($filename); ?></code><br>
            <strong>Command:</strong> <code><?php echo htmlspecialchars($cmd ?: 'none'); ?></code>
        </div>
        
        <div class="output-box">
            <pre><?php
// Capture output
ob_start();
include($file_path);
$output = ob_get_clean();
echo htmlspecialchars($output);
            ?></pre>
        </div>
        
        <div class="success-note">
            <strong>✓ Command executed successfully</strong><br>
            If you captured the flag, congratulations! Stage 5 complete.
        </div>
        
        <div class="back-link">
            <a href="upload.php" class="btn">Upload Another File</a>
            <a href="flow.php" class="btn">Back to Challenge Flow</a>
        </div>
    </div>
</body>
</html>
