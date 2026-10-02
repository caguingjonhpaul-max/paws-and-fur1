<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

$user_id = $_SESSION['user_id'];
$pet_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    DELETE FROM pets
    WHERE pet_id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $pet_id, $user_id);

if ($stmt->execute()) {

    header("Location: index.php");
    exit();

} else {

    echo "Error deleting pet.";

}

?>