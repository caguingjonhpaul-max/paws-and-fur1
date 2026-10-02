<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth("Client");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit();
}

pet_check_csrf();
$pet = pet_form_values($_POST);
$errors = pet_form_errors($pet);

if ($errors) {
    pet_flash_form($errors, $pet);
    header('Location: create.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$birth_date = $pet['birth_date'] === '' ? null : $pet['birth_date'];
$stmt = $conn->prepare('INSERT INTO pets (user_id, pet_name, species, breed, gender, birth_date, color) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('issssss', $user_id, $pet['pet_name'], $pet['species'], $pet['breed'], $pet['gender'], $birth_date, $pet['color']);

try {
    $saved = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    $saved = false;
}
if (!$saved) {
    pet_flash_form(['Unable to save this pet. Please try again.'], $pet);
    header('Location: create.php');
    exit();
}

$pet_id = $conn->insert_id;
$stmt->close();
header('Location: view.php?id=' . $pet_id);
exit();
