<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth();
if (!in_array($_SESSION['role'] ?? null, ['Staff', 'Administrator'], true)) {
    http_response_code(403);
    exit('Access denied.');
}
$can_update_status = $_SESSION['role'] === 'Staff';
$dashboard_url = $can_update_status ? '../dashboard.php' : '../../admin/dashboard.php';

$stmt = $conn->prepare("
    SELECT
        appointments.appointment_id,
        users.full_name AS client_name,
        pets.pet_name,
        pets.species,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.reason,
        appointments.status,
        appointments.rejection_reason,
        appointments.staff_id
    FROM appointments

    INNER JOIN users
        ON appointments.user_id = users.user_id

    INNER JOIN pets
        ON appointments.pet_id = pets.pet_id

    ORDER BY
        appointments.appointment_date ASC,
        appointments.appointment_time ASC
");

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Appointments - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="record-page">
<main class="record-card">

    <h1>Appointments</h1>
    <?php if (isset($_GET['rescheduled'])): ?>
        <p class="success-message">Appointment rescheduled successfully.</p>
    <?php endif; ?>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION['full_name']) ?>
    </p>

    <a href="<?= $dashboard_url ?>">
        Back to Dashboard
    </a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($appointment = $result->fetch_assoc()): ?>

            <article class="pet-card">

                <h3>
                    Appointment #<?= $appointment['appointment_id'] ?>
                </h3>

                <p>
                    Client:
                    <?= htmlspecialchars($appointment['client_name']) ?>
                </p>

                <p>
                    Pet:
                    <?= htmlspecialchars($appointment['pet_name']) ?>
                    -
                    <?= htmlspecialchars($appointment['species']) ?>
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
                    Reason:
                    <?= htmlspecialchars($appointment['reason']) ?>
                </p>

                <p>
                    Status:
                    <strong>
                        <?= htmlspecialchars($appointment['status']) ?>
                    </strong>
                </p>
                <?php if ($appointment['status'] === 'Rejected'): ?>

    <p>
        Rejection Reason:
        <?= htmlspecialchars($appointment['rejection_reason']) ?>
    </p>

<?php endif; ?>
                <?php if (in_array($appointment['status'], ['Pending', 'Approved', 'Confirmed'], true)): ?>
                    <p><a href="../../appointments/reschedule.php?id=<?= (int) $appointment['appointment_id'] ?>">Reschedule</a></p>
                <?php endif; ?>
                <?php if ($can_update_status && $appointment['status'] === 'Pending'): ?>

                <a href="approve.php?id=<?= $appointment['appointment_id'] ?>">
                    Approve
                </a>

                |

                <a href="reject.php?id=<?= $appointment['appointment_id'] ?>">
                    Reject
                </a>

                <?php elseif ($can_update_status && (
                    $appointment['status'] === 'Approved' ||
                    $appointment['status'] === 'Confirmed')
                ): ?>

                <a href="update_status.php?id=<?= $appointment['appointment_id'] ?>">
                    Update Status
                </a>

            <?php endif; ?>

            </article>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>
            No appointments found.
        </p>

    <?php endif; ?>

</main>
</body>

</html>
