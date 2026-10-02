<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth('Client');

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT full_name, username, email FROM users WHERE user_id = ? AND role = ?');
$role = 'Client';
$stmt->bind_param('is', $user_id, $role);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$owner) {
    http_response_code(404);
    exit('Owner account not found.');
}

$errors = $_SESSION['owner_form_errors'] ?? [];
$values = $_SESSION['owner_form_values'] ?? [];
unset($_SESSION['owner_form_errors'], $_SESSION['owner_form_values']);
$owner = array_merge($owner, $values);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Owner Information - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page">
    <main class="record-card">
        <h1>Pet owner information</h1>
        <p>Your pets are linked to this account. The owner information below appears with each pet record.</p>
        <?php if (isset($_GET['saved'])): ?><p class="success-message">Owner information saved.</p><?php endif; ?>
        <?php foreach ($errors as $error): ?><p class="error-message"><?= pet_escape($error) ?></p><?php endforeach; ?>
        <form action="update_process.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= pet_escape(pet_csrf_token()) ?>">
            <div class="form-group"><label for="full_name">Full name</label><input id="full_name" name="full_name" required maxlength="150" value="<?= pet_escape($owner['full_name']) ?>"></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required maxlength="255" value="<?= pet_escape($owner['email']) ?>"></div>
            <div class="form-group"><label for="username">Account username</label><input id="username" value="<?= pet_escape($owner['username']) ?>" readonly></div>
            <button type="submit" class="auth-button">Save owner information</button>
        </form>
        <p class="record-actions"><a href="../pets/index.php">My pets</a><a href="../dashboard.php">Dashboard</a></p>
    </main>
</body>
</html>
