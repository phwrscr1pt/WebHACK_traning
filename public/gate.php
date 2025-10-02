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
        'hint' => 'Set User-Agent: LOCTHBrowser',
        'message' => 'Only LOCTH Browser is allowed here!'
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
        'hint' => 'Set Date header with year 2018 (e.g., Wed, 21 Oct 2018 07:28:00 GMT)',
        'message' => 'This site only works in 2018. Time travel required!'
    ],
    4 => [
        'check' => function() {
            return isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1';
        },
        'hint' => 'Set DNT: 1',
        'message' => 'We don\'t trust users who allow tracking!'
    ],
    5 => [
        'check' => function() {
            if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return false;
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            return preg_match('/^51\.(68|75|91)\./', $ip);
        },
        'hint' => 'Set X-Forwarded-For with French IP starting with 51.68, 51.75, or 51.91',
        'message' => 'Only visitors from France (specific IP ranges) are welcome!'
    ],
    6 => [
        'check' => function() {
            return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && 
                   preg_match('/^fr-FR|^fr/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        },
        'hint' => 'Set Accept-Language: fr-FR,fr;q=0.9',
        'message' => 'Parlez-vous français? We only accept French speakers!'
    ]
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
    </head>
    <body>
        <div class="container">
            <h1>All Gates Passed!</h1>
            <div class="flag-box">
                <h2>Flag #1</h2>
                <code><?php echo htmlspecialchars($flag); ?></code>
            </div>
            <p>Congratulations! You've successfully navigated all 6 header gates.</p>
            <a href="flow.php" class="btn">Continue to Stage 2</a>
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
</head>
<body>
    <div class="container">
        <h1>Gate <?php echo $current_gate; ?>/6 Failed</h1>
        <div class="error-box">
            <p><?php echo htmlspecialchars($gates[$current_gate]['message']); ?></p>
        </div>
        <div class="hint-box">
            <strong>Hint:</strong> <?php echo htmlspecialchars($gates[$current_gate]['hint']); ?>
        </div>
        <p>Use Burp Suite to modify your HTTP headers and try again.</p>
        <?php if ($current_gate > 1): ?>
        <p class="success-hint">Gates 1-<?php echo $current_gate - 1; ?> passed!</p>
        <?php endif; ?>
    </div>
</body>
</html>
