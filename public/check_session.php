<?php
session_start();
echo "<h2>Session Debug</h2>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "<br>";
echo "Progress: " . ($_SESSION['progress'] ?? 'NOT SET') . "<br>";
echo "QA Mode: " . ($_SESSION['qa'] ?? 'NOT SET') . "<br>";
echo "<br><a href='note.php?id=555'>Enable QA Mode</a>";
?>
