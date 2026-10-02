<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        appointments.appointment_id,
        pets.pet_name,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.reason,
        appointments.status
    FROM appointments
    INNER JOIN pets
        ON appointments.pet_id = pets.pet_id
    WHERE appointments.user_id = ?
    ORDER BY appointments.appointment_date DESC,
             appointments.appointment_time DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Appointments - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="record-page">
<main class="record-card">

    <h1>My Appointments</h1>
    <?php if (isset($_GET['rescheduled'])): ?>
        <p class="success-message">Appointment rescheduled successfully.</p>
    <?php endif; ?>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION['full_name']) ?>
    </p>

    <a href="create.php">
        Request Appointment
    </a>

    <a href="../dashboard.php">
        Back to Dashboard
    </a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($appointment = $result->fetch_assoc()): ?>

            <article class="pet-card">

                <h3>
                    <?= htmlspecialchars($appointment['pet_name']) ?>
                </h3>

                <p>
                    Date:
                    <?= htmlspecialchars($appointment['appointment_date']) ?>
                </p>

                <p>
                    Time:
                    <?= htmlspecialchars($appointment['appointment_time']) ?>
                </p>

                <p>
                    Reason:
                    <?= htmlspecialchars($appointment['reason']) ?>
                </p>

                <p>
                    Status:
                    <strong>
                        <?= htmlspecialchars($appointment['status']) ?>
                    </strong>
                </p>
                <?php if (in_array($appointment['status'], ['Pending', 'Approved', 'Confirmed'], true)): ?>
                    <p><a href="../../appointments/reschedule.php?id=<?= (int) $appointment['appointment_id'] ?>">Reschedule</a></p>
                <?php endif; ?>

            </article>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>
            You don't have any appointments yet.
        </p>

    <?php endif; ?>

</main>
</body>

</html>
