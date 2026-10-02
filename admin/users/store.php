<?php
require_once '../../includes/admin_auth.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit();
}
if (empty($_SESSION['admin_create_csrf'])
    || !hash_equals($_SESSION['admin_create_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('Invalid form submission.');
}

$values = [
    'full_name' => trim((string) ($_POST['full_name'] ?? '')),
    'username' => trim((string) ($_POST['username'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'role' => (string) ($_POST['role'] ?? ''),
];
$password = (string) ($_POST['password'] ?? '');
$confirmation = (string) ($_POST['confirm_password'] ?? '');
$errors = [];
if ($values['full_name'] === '' || mb_strlen($values['full_name']) > 150) {
    $errors[] = 'Full name is required and must be at most 150 characters.';
}
if (!preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $values['username'])) {
    $errors[] = 'Username must be 3–50 letters, numbers, dots, underscores, or hyphens.';
}
if (strlen($values['email']) > 255 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if (!in_array($values['role'], ['Administrator', 'Staff'], true)) {
    $errors[] = 'Choose Administrator or Staff.';
}
if (strlen($password) < 8 || strlen($password) > 72) {
    $errors[] = 'Password must be 8–72 characters.';
}
if ($password !== $confirmation) {
    $errors[] = 'Passwords do not match.';
}

if (!$errors) {
    $stmt = $conn->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
    $stmt->bind_param('ss', $values['username'], $values['email']);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($existing as $account) {
        if (strcasecmp($account['username'], $values['username']) === 0) {
            $errors[] = 'That username is already in use.';
        }
        if (strcasecmp($account['email'], $values['email']) === 0) {
            $errors[] = 'That email is already in use.';
        }
    }
}
if ($errors) {
    $_SESSION['admin_create_errors'] = array_unique($errors);
    $_SESSION['admin_create_values'] = $values;
    header('Location: create.php');
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$status = 'Active';
$stmt = $conn->prepare('INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssss', $values['full_name'], $values['username'], $values['email'], $hash, $values['role'], $status);
try {
    $created = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    if ($exception->getCode() === 1062) {
        $created = false;
        $errors[] = 'That username or email is already in use.';
    } else {
        throw $exception;
    }
}
$stmt->close();
if (!$created) {
    $_SESSION['admin_create_errors'] = $errors ?: ['Unable to create the account. Please try again.'];
    $_SESSION['admin_create_values'] = $values;
    header('Location: create.php');
    exit();
}
unset($_SESSION['admin_create_csrf']);
header('Location: index.php?created=1');
exit();
