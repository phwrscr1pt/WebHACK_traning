<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LOCTH Web Security Lab</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
            background: white;
            border-radius: 20px;
            padding: 60px 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            font-size: 3em;
            color: #333;
            margin-bottom: 15px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 50px;
        }
        
        .stages {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin: 40px 0;
        }
        
        .stage {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px 20px;
            border-radius: 15px;
            font-size: 2em;
            font-weight: bold;
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .stage:hover {
            transform: translateY(-5px);
        }
        
        .stage-label {
            font-size: 0.4em;
            margin-top: 10px;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            padding: 18px 40px;
            margin: 15px 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .info {
            display: inline-block;
            margin: 0 20px;
            color: #999;
            font-size: 0.95em;
        }
        
        @media (max-width: 768px) {
            .stages {
                grid-template-columns: repeat(3, 1fr);
            }
            
            h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LOCTH Lab</h1>
        <p class="subtitle">5-Stage Web Security Challenge</p>
        
        <div class="stages">
            <div class="stage">
                1
                <div class="stage-label">Headers</div>
            </div>
            <div class="stage">
                2
                <div class="stage-label">SQLi</div>
            </div>
            <div class="stage">
                3
                <div class="stage-label">UNION</div>
            </div>
            <div class="stage">
                4
                <div class="stage-label">IDOR</div>
            </div>
            <div class="stage">
                5
                <div class="stage-label">Upload</div>
            </div>
        </div>
        
        <div style="margin: 40px 0;">
            <span class="info">5 Flags</span>
            <span class="info">•</span>
            <span class="info">Use Burp Suite</span>
            <span class="info">•</span>
            <span class="info">Format: LOCTH{...}</span>
        </div>
        
        <div>
            <a href="flow.php" class="btn">Start Challenge</a>
        </div>
    </div>
</body>
</html>