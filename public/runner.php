<?php
session_start();

// Check QA mode
if (!isset($_SESSION['qa']) || $_SESSION['qa'] !== 1) {
    http_response_code(403);
    die('Access Denied: QA mode not enabled');
}

$filename = $_GET['f'] ?? '';
$cmd = $_GET['cmd'] ?? '';

if (empty($filename)) {
    die('Error: No filename specified. Use ?f=filename.php&cmd=your_command');
}

// Basic path traversal protection
$filename = basename($filename);

// Only allow .php files
if (substr($filename, -4) !== '.php') {
    die('Error: Only .php files can be executed via runner');
}

$file_path = __DIR__ . '/uploads/' . $filename;

if (!file_exists($file_path)) {
    die('Error: File not found: ' . htmlspecialchars($filename));
}

// Pass cmd parameter to the uploaded PHP file
$_GET['cmd'] = $cmd;

// Execute the PHP file
echo "<!-- Executing: $filename with cmd=$cmd -->\n";
echo "<pre>";
include($file_path);
echo "</pre>";

echo "\n<hr>\n";
echo "<p><strong>Command executed.</strong> If this was a shell, your output should appear above.</p>";
echo "<p>Try: <code>?f=$filename&cmd=cat%20../flags/final_flag.txt</code></p>";
?>
