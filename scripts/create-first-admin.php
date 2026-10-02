<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

require_once __DIR__ . '/../config/database.php';

$admin_count = (int) $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Administrator'")
    ->fetch_assoc()['total'];
if ($admin_count > 0) {
    fwrite(STDOUT, "An administrator already exists. Sign in and use Admin > Users to create more accounts.\n");
    exit(0);
}

function first_admin_prompt(string $label): string
{
    fwrite(STDOUT, $label . ': ');
    $line = fgets(STDIN);
    if ($line === false) {
        fwrite(STDERR, "Input cancelled. No account was created.\n");
        exit(1);
    }
    return trim($line);
}

$full_name = first_admin_prompt('Full name');
$username = first_admin_prompt('Username (3-50 letters, numbers, dots, underscores, or hyphens)');
$email = first_admin_prompt('Email');

$errors = [];
if ($full_name === '' || mb_strlen($full_name) > 150) {
    $errors[] = 'Full name is required and must be at most 150 characters.';
}
if (!preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $username)) {
    $errors[] = 'Username must be 3-50 letters, numbers, dots, underscores, or hyphens.';
}
if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\nNo account was created.\n");
    exit(1);
}

$stmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();
if ($exists) {
    fwrite(STDERR, "Username or email already exists. No account was created.\n");
    exit(1);
}

$password = bin2hex(random_bytes(16));
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'Administrator';
$status = 'Active';
$stmt = $conn->prepare('INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssss', $full_name, $username, $email, $hash, $role, $status);
try {
    $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    if ($exception->getCode() === 1062) {
        fwrite(STDERR, "Username or email was just registered. No account was created.\n");
        exit(1);
    }
    throw $exception;
}
$stmt->close();

fwrite(STDOUT, "\nFirst administrator created.\nUsername: $username\nPassword: $password\n");
fwrite(STDOUT, "Save the password now; it will not be shown again.\n");
