<?php
require_once '../../includes/admin_auth.php';

if (empty($_SESSION['admin_create_csrf'])) {
    $_SESSION['admin_create_csrf'] = bin2hex(random_bytes(32));
}
$errors = $_SESSION['admin_create_errors'] ?? [];
$values = $_SESSION['admin_create_values'] ?? [];
unset($_SESSION['admin_create_errors'], $_SESSION['admin_create_values']);
function create_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin or Staff - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card">
    <span class="eyebrow">Administration</span>
    <h1>Create Admin or Staff account</h1>
    <p>Only administrators can create these accounts.</p>
    <?php foreach ($errors as $error): ?>
        <p class="error-message"><?= create_escape($error) ?></p>
    <?php endforeach; ?>
    <form action="store.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= create_escape($_SESSION['admin_create_csrf']) ?>">
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" maxlength="150" required value="<?= create_escape($values['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" title="3–50 letters, numbers, dots, underscores, or hyphens" required value="<?= create_escape($values['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="255" required value="<?= create_escape($values['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select a role</option>
                <option value="Administrator" <?= ($values['role'] ?? '') === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                <option value="Staff" <?= ($values['role'] ?? '') === 'Staff' ? 'selected' : '' ?>>Staff</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
            <small>At least 8 characters.</small>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        </div>
        <button class="auth-button" type="submit">Create account</button>
    </form>
    <p class="record-actions"><a href="index.php">Back to users</a></p>
</main></body></html>
