<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LOCTH Web Security Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔒 LOCTH Web Security Lab</h1>
        <div class="intro">
            <p>Welcome to the LOCTH Web Security Laboratory. This is a 5-stage security challenge designed to test your web penetration testing skills.</p>
            
            <h2>Challenge Overview</h2>
            <ul>
                <li><strong>Stage 1:</strong> HTTP Header Manipulation</li>
                <li><strong>Stage 2:</strong> SQL Injection + OTP Bypass</li>
                <li><strong>Stage 3:</strong> UNION-based SQL Injection</li>
                <li><strong>Stage 4:</strong> IDOR Vulnerability</li>
                <li><strong>Stage 5:</strong> File Upload + RCE</li>
            </ul>

            <h2>Tools Recommended</h2>
            <ul>
                <li>Burp Suite (for HTTP manipulation and fuzzing)</li>
                <li>Web Browser with Developer Tools</li>
                <li>SQLMap (optional)</li>
            </ul>

            <h2>Rules</h2>
            <ul>
                <li>All flags follow the format: <code>LOCTH{...}</code></li>
                <li>Flags are static and do not change</li>
                <li>Progress through stages sequentially for the best experience</li>
                <li>Use the Flow page to track your progress</li>
            </ul>

            <div class="buttons">
                <a href="flow.php" class="btn btn-primary">Start Challenge (Flow View)</a>
                <a href="gate.php" class="btn btn-secondary">Stage 1: Header Gate</a>
            </div>
        </div>
    </div>
</body>
</html>
