<?php
session_start();

// Load environment variables
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

// Define gates
$gates = [
    1 => [
        'check' => function() {
            return isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] === 'LOCTHBrowser';
        },
        'hint' => 'Just try to do in HTTP Request Header',
        'message' => 'Only people who use the official LOCTHBrowser are allowed on this site!'
    ],
    2 => [
        'check' => function() {
            return isset($_SERVER['HTTP_REFERER']) && 
                   strpos($_SERVER['HTTP_REFERER'], 'http://prologue.lab.local/') === 0;
        },
        'hint' => 'Set Referer: http://prologue.lab.local/',
        'message' => 'We don\'t trust requests from unknown origins!'
    ],
    3 => [
        'check' => function() {
            if (!isset($_SERVER['HTTP_DATE'])) return false;
            $date = strtotime($_SERVER['HTTP_DATE']);
            $year = date('Y', $date);
            return $year == '2018';
        },
        'hint' => 'Just Edit Your Time to 2018',
        'message' => 'This site only works in 2018. Time travel required!'
    ],
    4 => [
        'check' => function() {
            return isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1';
        },
        'hint' => 'DNT???',
        'message' => 'We don\'t trust users who allow tracking!'
    ],
    5 => [
        'check' => function() {
            if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return false;
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            return preg_match('/^51\.(68|75|91)\./', $ip);
        },
        'hint' => 'French IP 51.68, 51.75, or 51.91',
        'message' => 'Only visitors from France are welcome!'
    ],
    6 => [
        'check' => function() {
            return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && 
                   preg_match('/^fr-FR|^fr/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        },
        'hint' => 'fr-FR,fr;q=0.9',
        'message' => 'Parlez-vous français? We only accept French speakers!'
    ]
];
// Use local images
$gate_images = [
    1 => 'images/gates/gate1.gif',
    2 => 'images/gates/gate2.gif',
    3 => 'images/gates/gate3.gif',
    4 => 'images/gates/gate4.gif',
    5 => 'images/gates/gate5.gif',
    6 => 'images/gates/gate6.gif'
];
// Check gates in sequence
$current_gate = 1;
$all_passed = true;

foreach ($gates as $gate_num => $gate) {
    if (!$gate['check']()) {
        $all_passed = false;
        $current_gate = $gate_num;
        break;
    }
}

// If all gates passed, show flag

if ($all_passed) {
    $_SESSION['progress'] = max($_SESSION['progress'] ?? 0, 1);
    $flag = $_ENV['FLAG1'] ?? 'LOCTH{header_sequence_ok}';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Gate Passed!</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .gate-container {
                max-width: 600px;
                background: #f5f5f5;
                padding: 60px 40px;
                text-align: center;
            }
            .success-message {
                color: #28a745;
                font-size: 1.5em;
                margin-bottom: 30px;
                font-weight: 500;
            }
        </style>
    </head>
    <body>
        <div class="gate-container">
            <div class="success-message">
                All gates passed successfully!
            </div>
            
            <div style="margin: 30px 0;">
                <img src="https://via.placeholder.com/600x200/28a745/ffffff?text=All+Gates+Passed!" 
                     alt="Success" 
                    style="max-width: 600px; width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            </div>
            
            <div style="background: #d4edda; padding: 25px; border-radius: 8px; margin: 30px 0;">
                <h2 style="margin-top: 0;">Flag #1</h2>
                <code style="font-size: 1.3em; color: #28a745; font-weight: bold;">
                    <?php echo htmlspecialchars($flag); ?>
                </code>
            </div>
            
            <a href="flow.php" class="btn" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;">
                Continue to Stage 2
            </a>
            
            <div style="color: #999; font-size: 0.9em; margin-top: 40px;">
                © LOCTH Lab
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Failed at a gate
http_response_code(403);
header('X-Hint: ' . $gates[$current_gate]['hint']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gate <?php echo $current_gate; ?> Failed</title>
    <link rel="stylesheet" href="css/style.css">
        <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gate-container {
            max-width: 600px;
            background: #f5f5f5;
            padding: 60px 40px;
            text-align: center;
            box-shadow: none;
            border-radius: 0;
        }
        .gate-message {
            color: #dc3545;
            font-size: 1.3em;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .gate-image {
            margin: 30px 0;
        }
        .gate-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .footer-text {
            color: #999;
            font-size: 0.9em;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="gate-container">
        <div class="gate-message">
            <?php echo htmlspecialchars($gates[$current_gate]['message']); ?>
        </div>
        
        <div class="gate-image">
        <img src="<?php echo $gate_images[$current_gate]; ?>" 
            alt="Gate <?php echo $current_gate; ?>" 
            style="max-width: 600px; width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
        </div>
        
        <div class="hint-box" style="background: white; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <strong>Hint:</strong><br>
            <?php echo htmlspecialchars($gates[$current_gate]['hint']); ?>
            
            <?php if ($current_gate > 1): ?>
            <p style="color: #28a745; margin-top: 15px;">
                ✓ Gates 1-<?php echo $current_gate - 1; ?> passed!
            </p>
            <?php endif; ?>
        </div>
        
        <div class="footer-text">
            © LOCTH Lab
        </div>
    </div>
</body>
</html>
