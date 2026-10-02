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
$pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
if (!$pet_id) {
    http_response_code(404);
    exit('Pet not found.');
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT pet_id FROM pets WHERE pet_id = ? AND user_id = ?');
$stmt->bind_param('ii', $pet_id, $user_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows === 1;
$stmt->close();
if (!$exists) {
    http_response_code(404);
    exit('Pet not found.');
}

$pet = pet_form_values($_POST);
$errors = pet_form_errors($pet);
if ($errors) {
    pet_flash_form($errors, $pet);
    header('Location: edit.php?id=' . $pet_id);
    exit();
}

$birth_date = $pet['birth_date'] === '' ? null : $pet['birth_date'];
$stmt = $conn->prepare('UPDATE pets SET pet_name = ?, species = ?, breed = ?, gender = ?, birth_date = ?, color = ? WHERE pet_id = ? AND user_id = ?');
$stmt->bind_param('ssssssii', $pet['pet_name'], $pet['species'], $pet['breed'], $pet['gender'], $birth_date, $pet['color'], $pet_id, $user_id);
try {
    $saved = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    $saved = false;
}
if (!$saved) {
    pet_flash_form(['Unable to update this pet. Please try again.'], $pet);
    header('Location: edit.php?id=' . $pet_id);
    exit();
}
$stmt->close();

header('Location: view.php?id=' . $pet_id);
exit();
