<?php
session_start();

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
    <title>Search - LOCTH Lab</title>
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
            max-width: 700px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 40px;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 10px;
            display: flex;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .search-box input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            font-size: 1.1em;
            border-radius: 50px;
            outline: none;
        }
        
        .search-box button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .search-box button:hover {
            transform: scale(1.05);
        }
        
        .error-box {
            background: white;
            color: #dc3545;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .results {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .results h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        table th:first-child {
            border-top-left-radius: 10px;
        }
        
        table th:last-child {
            border-top-right-radius: 10px;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .hint {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: #666;
            font-size: 0.95em;
        }
        
        .hint code {
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: monospace;
        }
        
        .no-results {
            text-align: center;
            color: #999;
            padding: 40px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Search</h1>
        
        <form method="GET" class="search-box">
            <input type="text" 
                   name="q" 
                   placeholder="Search username..." 
                   value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                   autofocus>
            <button type="submit">Search</button>
        </form>
        
        <?php if ($error): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($results)): ?>
        <div class="results">
            <h2>Results (<?php echo count($results); ?>)</h2>
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
        <?php elseif (isset($_GET['q']) && empty($results) && !$error): ?>
        <div class="results">
            <div class="no-results">No results found</div>
        </div>
        <?php endif; ?>
        
<!--        <div class="hint">
            <strong>Stage 3:</strong> UNION SQLi with 3 columns<br>
            Try: <code>' UNION SELECT id,username,password_clear FROM users-- -</code>
        </div>
    </div> -->
</body>
</html>
