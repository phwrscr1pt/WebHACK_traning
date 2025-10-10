<?php
session_start();

// Manually enable QA mode
$_SESSION['qa'] = 1;
$_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 4);

echo "QA Mode Enabled!<br>";
echo "Progress: " . $_SESSION['progress'] . "<br>";
echo "QA Status: " . $_SESSION['qa'] . "<br><br>";
echo "<a href='upload.php'>Go to Upload Page</a>";
?>
