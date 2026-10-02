<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth('Client');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

pet_check_csrf();
$full_name = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$errors = [];

if ($full_name === '' || mb_strlen($full_name) > 150) {
    $errors[] = 'Full name is required and must be at most 150 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    $errors[] = 'Enter a valid email address.';
}

$user_id = (int) $_SESSION['user_id'];
if (!$errors) {
    $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1');
    $stmt->bind_param('si', $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'That email address is already registered.';
    }
    $stmt->close();
}

if ($errors) {
    $_SESSION['owner_form_errors'] = $errors;
    $_SESSION['owner_form_values'] = ['full_name' => $full_name, 'email' => $email];
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ? WHERE user_id = ? AND role = ?');
$role = 'Client';
$stmt->bind_param('ssis', $full_name, $email, $user_id, $role);
try {
    $saved = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    $saved = false;
}
if (!$saved) {
    $_SESSION['owner_form_errors'] = ['Unable to save owner information. Please try again.'];
    $_SESSION['owner_form_values'] = ['full_name' => $full_name, 'email' => $email];
    header('Location: index.php');
    exit();
}
$stmt->close();

$_SESSION['full_name'] = $full_name;
$_SESSION['email'] = $email;
header('Location: index.php?saved=1');
exit();
