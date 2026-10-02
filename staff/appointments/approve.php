<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Staff");

$appointment_id = $_GET['id'] ?? 0;

$staff_id = $_SESSION['user_id'];


// Check if the appointment exists and is still Pending
$stmt = $conn->prepare("
    SELECT appointment_id
    FROM appointments
    WHERE appointment_id = ?
    AND status = 'Pending'
");

$stmt->bind_param("i", $appointment_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    die("Appointment not found or has already been processed.");

}


// Approve appointment and assign the logged-in staff
$stmt = $conn->prepare("
    UPDATE appointments
    SET
        status = 'Approved',
        staff_id = ?
    WHERE appointment_id = ?
    AND status = 'Pending'
");

$stmt->bind_param(
    "ii",
    $staff_id,
    $appointment_id
);

if ($stmt->execute()) {

    echo "
        <script>
            alert('Appointment approved successfully!');
            window.location.href = 'index.php';
        </script>
    ";

} else {

    echo "Error approving appointment: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>