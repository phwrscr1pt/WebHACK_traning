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
    <title>Curator Dashboard - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>📝 Curator Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <h2>My Notes</h2>
        <?php if (empty($notes)): ?>
        <p>No notes found.</p>
        <?php else: ?>
        <div class="notes-list">
            <?php foreach ($notes as $note): ?>
            <div class="note-item">
                <a href="note.php?id=<?php echo $note['id']; ?>">
                    📄 <?php echo htmlspecialchars($note['title']); ?> (ID: <?php echo $note['id']; ?>)
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="hint-box">
            <strong>Hint:</strong> Your notes have IDs 501, 502, 503. What if you tried accessing other IDs directly?<br>
            Try exploring <code>note.php?id=9001</code> 👀
        </div>
        
        <a href="flow.php" class="btn">Back to Flow</a>
    </div>
</body>
</html>
