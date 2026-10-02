<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Staff");

$appointment_id = $_GET['id'] ?? 0;

$staff_id = $_SESSION['user_id'];


// Check if appointment exists and is Pending
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reject Appointment - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="record-page">
<main class="record-card">

    <h1>Reject Appointment</h1>

    <form action="reject.php?id=<?= $appointment_id ?>" method="POST">

        <div class="form-group">

            <label for="rejection_reason">
                Reason for Rejection
            </label>

            <textarea
                id="rejection_reason"
                name="rejection_reason"
                rows="5"
                placeholder="Enter the reason for rejecting this appointment"
                required
            ></textarea>

        </div>

        <button
            type="submit"
            class="auth-button"
        >
            Reject Appointment
        </button>

    </form>

    <br>

    <a href="index.php">
        Cancel
    </a>

</main>
</body>

</html>

<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rejection_reason = trim($_POST['rejection_reason']);

    if (empty($rejection_reason)) {
        die("Please provide a reason for rejection.");
    }


    // Update appointment
    $stmt = $conn->prepare("
        UPDATE appointments
        SET
            status = 'Rejected',
            rejection_reason = ?,
            staff_id = ?
        WHERE appointment_id = ?
        AND status = 'Pending'
    ");

    $stmt->bind_param(
        "sii",
        $rejection_reason,
        $staff_id,
        $appointment_id
    );


    if ($stmt->execute()) {

        echo "
            <script>
                alert('Appointment rejected successfully!');
                window.location.href = 'index.php';
            </script>
        ";

        exit();

    } else {

        echo "Error rejecting appointment: " . $stmt->error;

    }

}

?>
