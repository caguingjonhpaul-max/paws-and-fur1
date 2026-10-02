<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

$user_id = $_SESSION['user_id'];

$pet_name = trim($_POST['pet_name']);
$species = trim($_POST['species']);
$breed = trim($_POST['breed']);
$gender = trim($_POST['gender']);
$birth_date = !empty($_POST['birth_date'])
    ? $_POST['birth_date']
    : null;
$color = trim($_POST['color']);

$stmt = $conn->prepare("
    INSERT INTO pets
    (user_id, pet_name, species, breed, gender, birth_date, color)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issssss",
    $user_id,
    $pet_name,
    $species,
    $breed,
    $gender,
    $birth_date,
    $color
);

if ($stmt->execute()) {

    echo "
        <script>
            alert('Pet added successfully!');
            window.location.href = 'index.php';
        </script>
    ";

} else {

    echo "Error adding pet: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>