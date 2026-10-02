<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

$user_id = $_SESSION['user_id'];

$pet_id = $_POST['pet_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$reason = trim($_POST['reason']);


// Make sure the selected pet belongs to this client
$stmt = $conn->prepare("
    SELECT pet_id
    FROM pets
    WHERE pet_id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $pet_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    die("Invalid pet selection.");

}


// Check if the time slot is already occupied
$stmt = $conn->prepare("
    SELECT appointment_id
    FROM appointments
    WHERE appointment_date = ?
    AND appointment_time = ?
    AND status IN ('Pending', 'Approved', 'Confirmed')
");

$stmt->bind_param(
    "ss",
    $appointment_date,
    $appointment_time
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    die("This appointment time is already unavailable.");

}


// Create appointment
$status = "Pending";

$stmt = $conn->prepare("
    INSERT INTO appointments
    (
        user_id,
        pet_id,
        staff_id,
        appointment_date,
        appointment_time,
        reason,
        status
    )
    VALUES (?, ?, NULL, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iissss",
    $user_id,
    $pet_id,
    $appointment_date,
    $appointment_time,
    $reason,
    $status
);

if ($stmt->execute()) {

    echo "
        <script>
            alert('Appointment request submitted successfully!');
            window.location.href = 'index.php';
        </script>
    ";

} else {

    echo "Error creating appointment: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>