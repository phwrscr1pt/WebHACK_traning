<?php
session_start();

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
        
        h1 {
            color: white;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 50px;
        }
        
        .stage {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .stage.locked {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .stage.completed {
            background: #d4edda;
            border-left: 5px solid #28a745;
        }
        
        .stage-number {
            font-size: 3em;
            font-weight: bold;
            color: #667eea;
            min-width: 60px;
        }
        
        .stage.completed .stage-number {
            color: #28a745;
        }
        
        .stage-content {
            flex: 1;
        }
        
        .stage-title {
            font-size: 1.5em;
            margin-bottom: 8px;
            color: #333;
        }
        
        .stage-status {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .completion {
            background: white;
            border-radius: 15px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .completion h2 {
            color: #28a745;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .stage {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Challenge Progress</h1>

        <!-- Stage 1 -->
        <div class="stage <?php echo $progress >= 1 ? 'completed' : ''; ?>">
            <div class="stage-number">1</div>
            <div class="stage-content">
                <div class="stage-title">HTTP Headers</div>
                <?php if ($progress >= 1): ?>
                    <div class="stage-status">✓ Completed</div>
                <?php else: ?>
                    <a href="gate.php" class="btn">Start</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stage 2 -->
        <div class="stage <?php echo $progress >= 2 ? 'completed' : ($progress >= 1 ? '' : 'locked'); ?>">
            <div class="stage-number">2</div>
            <div class="stage-content">
                <div class="stage-title">SQL Injection + OTP</div>
                <?php if ($progress >= 2): ?>
                    <div class="stage-status">✓ Completed</div>
                <?php elseif ($progress >= 1): ?>
                    <a href="login.php" class="btn">Start</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stage 3 -->
        <div class="stage <?php echo $progress >= 3 ? 'completed' : ($progress >= 2 ? '' : 'locked'); ?>">
            <div class="stage-number">3</div>
            <div class="stage-content">
                <div class="stage-title">UNION SQLi</div>
                <?php if ($progress >= 3): ?>
                    <div class="stage-status">✓ Completed</div>
                <?php elseif ($progress >= 2): ?>
                    <a href="search.php" class="btn">Start</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stage 4 -->
        <div class="stage <?php echo $progress >= 4 ? 'completed' : ($progress >= 3 ? '' : 'locked'); ?>">
            <div class="stage-number">4</div>
            <div class="stage-content">
                <div class="stage-title">IDOR</div>
                <?php if ($progress >= 4): ?>
                    <div class="stage-status">✓ Completed</div>
                <?php elseif ($progress >= 3): ?>
                    <a href="curator.php" class="btn">Start</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stage 5 -->
        <div class="stage <?php echo $progress >= 5 ? 'completed' : ($progress >= 4 ? '' : 'locked'); ?>">
            <div class="stage-number">5</div>
            <div class="stage-content">
                <div class="stage-title">File Upload + RCE</div>
                <?php if ($progress >= 5): ?>
                    <div class="stage-status">✓ Completed</div>
                <?php elseif ($progress >= 4): ?>
                    <a href="upload.php" class="btn">Start</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($progress >= 5): ?>
        <div class="completion">
            <h2>🏆 All Stages Complete!</h2>
            <p style="font-size: 1.3em; color: #666; margin-top: 10px;">5/5 Flags Captured</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>