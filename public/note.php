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
            if ($note_id == 9001) {
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
    <title>Note Viewer - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>📄 Note Viewer</h1>
        
        <?php if ($note): ?>
        <div class="note-view">
            <h2><?php echo htmlspecialchars($note['title']); ?></h2>
            <div class="note-content">
                <?php echo nl2br(htmlspecialchars($note['content'])); ?>
            </div>
            <p class="note-meta">Note ID: <?php echo $note['id']; ?></p>
        </div>
        
        <?php if ($note_id == 9001): ?>
        <div class="success-box">
            <strong>🎉 QA Mode Enabled!</strong> You can now access the file upload feature for Stage 5.
            <br><a href="flow.php" class="btn">Continue to Stage 5</a>
        </div>
        <?php endif; ?>
        
        <?php elseif ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
        <p>No note ID specified.</p>
        <?php endif; ?>
        
        <a href="curator.php" class="btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
