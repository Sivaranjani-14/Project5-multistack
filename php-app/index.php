<?php
require_once 'application.php';

$message = '';
$statusClass = '';
$users = [];

try {
    $pdo = getDBConnection();

    // 1. Core Logic: Process Incoming Form Submissions (POST Method)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if (!empty($username) && !empty($email)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Prepared statement to prevent SQL Injection vectors securely
                $stmt = $pdo->prepare('INSERT INTO users (username, email) VALUES (?, ?)');
                $stmt->execute([$username, $email]);
                
                $message = "User data synchronized successfully with AWS RDS multi-AZ instance!";
                $statusClass = "success";
            } else {
                $message = "Error: Invalid email structure provided.";
                $statusClass = "error";
            }
        } else {
            $message = "Error: All data payload fields are mandatory.";
            $statusClass = "error";
        }
    }

    // 2. Fetch Active Dataset Records
    $query = $pdo->query('SELECT username, email, created_at FROM users ORDER BY created_at DESC');
    $users = $query->fetchAll();

} catch (\Exception $e) {
    $message = "Infrastructure Error: " . $e->getMessage();
    $statusClass = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP 8.4 User Registry Service</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { color: #1e293b; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        h3 { color: #334155; margin-top: 30px; }
        .alert { padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; font-weight: 500; }
        .success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #475569; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        input[type="text"]:focus, input[type="email"]:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        button { background-color: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 14px; }
        button:hover { background-color: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #f8fafc; font-weight: 600; color: #64748b; }
        tr:hover { background-color: #f8fafc; }
        .empty-state { text-align: center; color: #94a3b8; padding: 20px; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <h2>AWS Multi-Stack App: PHP User Registry</h2>
    
    <?php if ($message): ?>
        <div class="alert <?= $statusClass ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="e.g. john_doe" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="e.g. john@example.com" required>
        </div>
        <button type="submit">Commit to Database</button>
    </form>

    <h3>Active Synchronized Users (Live from RDS Cluster)</h3>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Email Address</th>
                <th>Registration Date (UTC)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="3" class="empty-state">No user records currently synchronized to this database topology.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>