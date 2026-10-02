<?php
require_once '../../includes/admin_auth.php';
require_once '../../config/database.php';

$users = $conn->query('SELECT user_id, full_name, username, email, role, status, created_at FROM users ORDER BY created_at DESC, user_id DESC')->fetch_all(MYSQLI_ASSOC);
function user_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card vaccination-card">
    <span class="eyebrow">Administration</span>
    <h1>User accounts</h1>
    <p class="record-actions">
        <a class="button" href="create.php">Create Admin or Staff</a>
        <a href="../dashboard.php">Dashboard</a>
    </p>
    <?php if (isset($_GET['created'])): ?>
        <p class="success-message">Account created. The user can sign in with the username and password you set.</p>
    <?php endif; ?>
    <?php if (!$users): ?>
        <p>No accounts found.</p>
    <?php else: ?>
        <div class="table-scroll"><table class="record-table">
            <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= user_escape($user['full_name']) ?></td>
                    <td><?= user_escape($user['username']) ?></td>
                    <td><?= user_escape($user['email']) ?></td>
                    <td><?= user_escape($user['role']) ?></td>
                    <td><?= user_escape($user['status']) ?></td>
                    <td><?= user_escape($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</main></body></html>
