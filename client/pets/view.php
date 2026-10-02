<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth('Client');

$pet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pet_id) {
    http_response_code(404);
    exit('Pet not found.');
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT p.pet_id, p.pet_name, p.species, p.breed, p.gender, p.birth_date, p.color, u.full_name, u.email FROM pets p JOIN users u ON u.user_id = p.user_id WHERE p.pet_id = ? AND p.user_id = ?');
$stmt->bind_param('ii', $pet_id, $user_id);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$pet) {
    http_response_code(404);
    exit('Pet not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= pet_escape($pet['pet_name']) ?> - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page">
    <main class="record-card">
        <h1><?= pet_escape($pet['pet_name']) ?></h1>
        <h2>Pet information</h2>
        <dl class="record-details">
            <dt>Species</dt><dd><?= pet_escape($pet['species']) ?></dd>
            <dt>Breed</dt><dd><?= pet_escape($pet['breed'] ?: 'N/A') ?></dd>
            <dt>Gender</dt><dd><?= pet_escape($pet['gender']) ?></dd>
            <dt>Birth date</dt><dd><?= pet_escape($pet['birth_date'] ?: 'N/A') ?></dd>
            <dt>Color</dt><dd><?= pet_escape($pet['color'] ?: 'N/A') ?></dd>
        </dl>
        <h2>Pet owner</h2>
        <dl class="record-details">
            <dt>Name</dt><dd><?= pet_escape($pet['full_name']) ?></dd>
            <dt>Email</dt><dd><?= pet_escape($pet['email']) ?></dd>
        </dl>
        <p class="record-actions">
            <a href="edit.php?id=<?= $pet_id ?>">Edit pet</a>
            <a href="../owner/index.php">Edit owner information</a>
            <a href="index.php">Back to my pets</a>
        </p>
    </main>
</body>
</html>
