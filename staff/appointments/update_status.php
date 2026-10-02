<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Staff");

$appointment_id = $_GET['id'] ?? 0;


// Get appointment information
$stmt = $conn->prepare("
    SELECT
        appointment_id,
        appointment_date,
        appointment_time,
        status
    FROM appointments
    WHERE appointment_id = ?
");

$stmt->bind_param("i", $appointment_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Appointment not found.");
}

$appointment = $result->fetch_assoc();


// Process status update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_status = $_POST['status'];

    $allowed_statuses = [
        'Confirmed',
        'Completed',
        'Cancelled'
    ];

    if (!in_array($new_status, $allowed_statuses)) {
        die("Invalid appointment status.");
    }


    $stmt = $conn->prepare("
        UPDATE appointments
        SET status = ?
        WHERE appointment_id = ?
    ");

    $stmt->bind_param(
        "si",
        $new_status,
        $appointment_id
    );

    if ($stmt->execute()) {

        echo "
            <script>
                alert('Appointment status updated successfully!');
                window.location.href = 'index.php';
            </script>
        ";

        exit();

    } else {

        echo "Error updating appointment: " . $stmt->error;

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Appointment - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body>

    <h1>Update Appointment Status</h1>

    <p>
        Appointment #<?= $appointment['appointment_id'] ?>
    </p>

    <p>
        Date:
        <?= htmlspecialchars($appointment['appointment_date']) ?>
    </p>

    <p>
        Time:
        <?= htmlspecialchars($appointment['appointment_time']) ?>
    </p>

    <p>
        Current Status:
        <strong>
            <?= htmlspecialchars($appointment['status']) ?>
        </strong>
    </p>


    <form method="POST">

        <div class="form-group">

            <label for="status">
                New Status
            </label>

            <select
                id="status"
                name="status"
                required
            >

                <option value="">
                    Select Status
                </option>

                <option value="Confirmed">
                    Confirmed
                </option>

                <option value="Completed">
                    Completed
                </option>

                <option value="Cancelled">
                    Cancelled
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="auth-button"
        >
            Update Status
        </button>

    </form>

    <br>

    <a href="index.php">
        Back to Appointments
    </a>

</body>

</html>