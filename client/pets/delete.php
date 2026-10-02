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
$stmt = $conn->prepare('DELETE FROM pets WHERE pet_id = ? AND user_id = ?');
$stmt->bind_param('ii', $pet_id, $user_id);
try {
    $deleted = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    $deleted = false;
}
if (!$deleted) {
    http_response_code(409);
    exit('Unable to delete this pet. It may have related appointments.');
}
$stmt->close();
header('Location: index.php');
exit();
