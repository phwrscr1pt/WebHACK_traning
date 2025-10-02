<?php
session_start();

// Initialize progress if not set
if (!isset($_SESSION['progress'])) {
    $_SESSION['progress'] = 0;
}

$progress = $_SESSION['progress'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Challenge Flow - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🎯 Challenge Progress</h1>
        <p class="subtitle">Follow the stages below. Complete each one to unlock the next!</p>

        <!-- Stage 1 -->
        <div class="card <?php echo $progress >= 1 ? 'completed' : 'active'; ?>">
            <h2>Stage 1: HTTP Header Manipulation</h2>
            <p>Navigate through the header gates by sending the correct HTTP headers in sequence.</p>
            <?php if ($progress >= 1): ?>
                <p class="success">✓ Completed! Flag obtained.</p>
            <?php else: ?>
                <p>Use Burp Suite to modify HTTP headers and pass all 6 gates.</p>
                <a href="gate.php" class="btn">Enter Stage 1</a>
            <?php endif; ?>
        </div>

        <!-- Stage 2 -->
        <?php if ($progress >= 1): ?>
        <div class="card <?php echo $progress >= 2 ? 'completed' : 'active'; ?>">
            <h2>Stage 2: SQL Injection + OTP Bypass</h2>
            <p>Exploit Boolean-based SQL injection to login as staff, then brute-force the OTP.</p>
            <?php if ($progress >= 2): ?>
                <p class="success">✓ Completed! Flag obtained.</p>
            <?php else: ?>
                <p>Hint: Look for Boolean-based SQLi in the login form. OTP range: 0000-0200.</p>
                <a href="login.php" class="btn">Enter Stage 2</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stage 3 -->
        <?php if ($progress >= 2): ?>
        <div class="card <?php echo $progress >= 3 ? 'completed' : 'active'; ?>">
            <h2>Stage 3: UNION-based SQL Injection</h2>
            <p>Use UNION SQLi to dump credentials and login as shadow_curator.</p>
            <?php if ($progress >= 3): ?>
                <p class="success">✓ Completed! Flag obtained.</p>
            <?php else: ?>
                <p>Hint: The search form is vulnerable. Use UNION SELECT with 3 columns.</p>
                <a href="search.php" class="btn">Enter Stage 3</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stage 4 -->
        <?php if ($progress >= 3): ?>
        <div class="card <?php echo $progress >= 4 ? 'completed' : 'active'; ?>">
            <h2>Stage 4: IDOR (Insecure Direct Object Reference)</h2>
            <p>Access notes that don't belong to you by manipulating the note ID.</p>
            <?php if ($progress >= 4): ?>
                <p class="success">✓ Completed! Flag obtained. QA Mode enabled!</p>
            <?php else: ?>
                <p>Hint: Your notes are 501-503. What about other IDs? Try 9001.</p>
                <a href="curator.php" class="btn">Enter Stage 4</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stage 5 -->
        <?php if ($progress >= 4): ?>
        <div class="card <?php echo $progress >= 5 ? 'completed' : 'active'; ?>">
            <h2>Stage 5: File Upload + Remote Code Execution</h2>
            <p>Bypass file upload restrictions and execute code to retrieve the final flag.</p>
            <?php if ($progress >= 5): ?>
                <p class="success">✓ Completed! All stages cleared! 🎉</p>
            <?php else: ?>
                <p>Hint: Use double extension (shell.php.jpg) + PNG magic bytes. Then use runner.php.</p>
                <a href="upload.php" class="btn">Enter Stage 5</a>
                <p class="hint-small">Example: <code>/runner.php?f=shell.php&cmd=cat%20../flags/final_flag.txt</code></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($progress >= 5): ?>
        <div class="completion">
            <h2>🏆 Congratulations!</h2>
            <p>You've completed all 5 stages of the LOCTH Web Security Lab!</p>
            <p>Total Flags Captured: 5/5</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
