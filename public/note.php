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

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$note_id = $_GET['id'] ?? 0;
$note = null;
$error = '';

if ($note_id) {
    $mysqli = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'locth_user',
        $_ENV['DB_PASS'] ?? 'L0cTh_S3cur3_P@ss',
        $_ENV['DB_NAME'] ?? 'locth_lab'
    );
    
    if (!$mysqli->connect_error) {
        // VULNERABLE: No owner check! (IDOR vulnerability)
        $query = "SELECT id, title, content FROM notes WHERE id = " . intval($note_id);
        $result = $mysqli->query($query);
        
        if ($result && $result->num_rows > 0) {
            $note = $result->fetch_assoc();
            
            // If accessing note 9001 (head_curator's secret note)
            if ($note_id == 555) {
                $_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 4);
                $_SESSION['qa'] = 1; // Enable QA mode for file upload
            }
        } else {
            $error = 'Note not found';
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Note - LOCTH Lab</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        
        .note-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .note-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .note-header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .note-meta {
            color: #999;
            font-size: 0.95em;
        }
        
        .note-content {
            color: #555;
            line-height: 1.8;
            font-size: 1.1em;
            white-space: pre-wrap;
        }
        
        .error-box {
            background: white;
            color: #dc3545;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .success-banner {
            background: #d4edda;
            color: #155724;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .success-banner h3 {
            color: #28a745;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .flag-highlight {
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.2em;
            display: inline-block;
            margin: 15px 0;
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            margin: 10px 5px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .note-card {
                padding: 25px;
            }
            
            .note-header h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($note): ?>
            <?php if ($note_id == 555): ?>
            <div class="success-banner">
                <h3>Stage 4 Complete!</h3>
                <p>QA Mode enabled for Stage 5</p>
            </div>
            <?php endif; ?>
            
            <div class="note-card">
                <div class="note-header">
                    <h1><?php echo htmlspecialchars($note['title']); ?></h1>
                    <div class="note-meta">Note ID: #<?php echo $note['id']; ?></div>
                </div>
                
                <div class="note-content">
                    <?php 
                    $content = htmlspecialchars($note['content']);
                    // Highlight the flag if present
                    if (strpos($content, 'LOCTH{') !== false) {
                        $content = preg_replace('/(LOCTH\{[^}]+\})/', '<span class="flag-highlight">$1</span>', $content);
                    }
                    echo $content;
                    ?>
                </div>
                
                <div class="actions">
                    <?php if ($note_id == 555): ?>
                    <a href="flow.php" class="btn">Continue to Stage 5</a>
                    <?php endif; ?>
                    <a href="curator.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        <?php elseif ($error): ?>
            <div class="error-box">
                <h2><?php echo htmlspecialchars($error); ?></h2>
                <div class="actions">
                    <a href="curator.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <div class="error-box">
                <h2>No note ID specified</h2>
                <div class="actions">
                    <a href="curator.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>