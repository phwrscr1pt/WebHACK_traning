<?php
session_start();
loadEnv(__DIR__ . '/../.env');

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$results = [];
$error = '';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    
    $mysqli = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'locth_user',
        $_ENV['DB_PASS'] ?? 'L0cTh_S3cur3_P@ss',
        $_ENV['DB_NAME'] ?? 'locth_lab'
    );
    
    if ($mysqli->connect_error) {
        $error = 'Database connection failed';
    } else {
        // Vulnerable to UNION-based SQLi (3 columns)
        $query = "SELECT id, username, role FROM users WHERE username LIKE '%$search%'";
        $result = $mysqli->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        } else {
            $error = 'Query error: ' . $mysqli->error;
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Search - LOCTH Lab</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔍 User Search</h1>
        
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="q" placeholder="Search username..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($results)): ?>
        <div class="results">
            <h2>Search Results:</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="hint-box">
            <strong>Hint:</strong> This search is vulnerable to UNION-based SQL injection.<br>
            The query has <strong>3 columns</strong>. Try: <code>' UNION SELECT id,username,password_clear FROM users-- -</code><br>
            Look for credentials of <strong>shadow_curator</strong>, then login at login.php
        </div>
    </div>
</body>
</html>
