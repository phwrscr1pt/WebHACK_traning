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

// Check if logged in as shadow_curator
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'shadow_curator') {
    header('Location: login.php');
    exit;
}

$mysqli = new mysqli(
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_USER'] ?? 'locth_user',
    $_ENV['DB_PASS'] ?? 'L0cTh_S3cur3_P@ss',
    $_ENV['DB_NAME'] ?? 'locth_lab'
);

// Get user's notes (only showing their own notes here)
$notes = [];
if (!$mysqli->connect_error) {
    $user_id = $_SESSION['user_id'];
    $result = $mysqli->query("SELECT id, title FROM notes WHERE user_id = $user_id ORDER BY id");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notes[] = $row;
        }
    }
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Curator - LOCTH Lab</title>
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
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .header .user-info {
            color: #666;
            font-size: 1.1em;
        }
        
        .notes-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .notes-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .note-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .note-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .note-card a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.1em;
        }
        
        .note-icon {
            font-size: 1.5em;
            margin-right: 15px;
        }
        
        .note-id {
            opacity: 0.8;
            font-size: 0.9em;
        }
        
        .hint-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .hint-box h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .hint-box p {
            color: #666;
            line-height: 1.6;
        }
        
        .hint-box code {
            background: #f8f9fa;
            padding: 3px 8px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: monospace;
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
            box-shadow: 0 5px 15px rgba(255,255,255,0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,255,255,0.5);
        }
        
        .empty-state {
            text-align: center;
            color: #999;
            padding: 40px;
            font-size: 1.1em;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Curator Dashboard</h1>
            <div class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        </div>
        
        <div class="notes-section">
            <h2>My Notes</h2>
            <?php if (empty($notes)): ?>
            <div class="empty-state">No notes found</div>
            <?php else: ?>
            <?php foreach ($notes as $note): ?>
            <div class="note-card">
                <a href="note.php?id=<?php echo $note['id']; ?>">
                    <span>
                        <span class="note-icon">📄</span>
                        <?php echo htmlspecialchars($note['title']); ?>
                    </span>
                    <span class="note-id">#<?php echo $note['id']; ?></span>
                </a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="hint-box">
            <h3>Stage 4: IDOR</h3>
            <p>Your notes: #501, #502, #503<br>
            Try accessing <code>note.php?id=9001</code></p>
        </div>
        
        <div class="back-link">
            <a href="flow.php" class="btn">Back to Flow</a>
        </div>
    </div>
</body>
</html>