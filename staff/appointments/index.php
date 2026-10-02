<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Staff");

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

<body>

    <h1>Appointments</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION['full_name']) ?>
    </p>

    <a href="../dashboard.php">
        Back to Dashboard
    </a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($appointment = $result->fetch_assoc()): ?>

            <div>

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
                <?php if ($appointment['status'] === 'Pending'): ?>

                <a href="approve.php?id=<?= $appointment['appointment_id'] ?>">
                    Approve
                </a>

                |

                <a href="reject.php?id=<?= $appointment['appointment_id'] ?>">
                    Reject
                </a>

                <?php elseif (
                    $appointment['status'] === 'Approved' ||
                    $appointment['status'] === 'Confirmed'
                ): ?>

                <a href="update_status.php?id=<?= $appointment['appointment_id'] ?>">
                    Update Status
                </a>

            <?php endif; ?>

            </div>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>
            No appointments found.
        </p>

    <?php endif; ?>

</body>

</html>