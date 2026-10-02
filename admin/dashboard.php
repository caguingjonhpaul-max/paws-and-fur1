<?php

require_once "../includes/admin_auth.php";
require_once "../config/database.php";

function admin_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$counts = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM users) AS users_total,
        (SELECT COUNT(*) FROM appointments) AS appointments_total,
        (SELECT COUNT(*) FROM pets) AS pets_total,
        (SELECT COUNT(*) FROM vaccinations) AS vaccinations_total
")->fetch_assoc();

$recent_users = $conn->query("SELECT full_name, role, status FROM users ORDER BY created_at DESC, user_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_appointments = $conn->query("
    SELECT a.appointment_date, a.appointment_time, a.status,
           p.pet_name, u.full_name AS owner_name
    FROM appointments a
    JOIN pets p ON p.pet_id = a.pet_id
    JOIN users u ON u.user_id = a.user_id
    ORDER BY a.created_at DESC, a.appointment_id DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
$recent_pets = $conn->query("
    SELECT p.pet_name, p.species, u.full_name AS owner_name
    FROM pets p
    JOIN users u ON u.user_id = p.user_id
    ORDER BY p.created_at DESC, p.pet_id DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrator Dashboard - PAWS AND FUR</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="dashboard-layout">

    <aside class="dashboard-sidebar">

        <div class="dashboard-logo">
            <h2>PAWS AND FUR</h2>
            <p>ADMINISTRATOR</p>
        </div>

        <nav>

            <a href="dashboard.php">Dashboard</a>

            <a href="#users">Users</a>

            <a href="../staff/appointments/index.php">Appointments</a>

            <a href="#pets">Pets</a>

            <a href="../staff/vaccinations/index.php">Vaccinations</a>


        </nav>

    </aside>


    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>
                <h1>Administrator Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="user-area">

            <span>Administrator</span>

            <a href="../auth/logout.php">
                Logout
            </a>

             </div>

        </header>


        <section class="dashboard-content">

            <div class="welcome-card">

                <h2>Welcome to PAWS AND FUR CLINIC</h2>

                <p>
                    View current accounts, appointments, pets, and vaccination records.
                </p>

            </div>


            <div class="dashboard-cards">

                <div class="dashboard-card">
                    <h3>Users</h3>
                    <p>Registered accounts.</p>
                    <strong><?= (int) $counts['users_total'] ?></strong>
                </div>


                <div class="dashboard-card">
                    <h3>Appointments</h3>
                    <p>Appointments in the system.</p>
                    <strong><?= (int) $counts['appointments_total'] ?></strong>
                </div>


                <div class="dashboard-card">
                    <h3>Pets</h3>
                    <p>View registered pets.</p>
                    <strong><?= (int) $counts['pets_total'] ?></strong>
                </div>


                <div class="dashboard-card">
                    <h3>Vaccinations</h3>
                    <p><a href="../staff/vaccinations/index.php">View records and schedules.</a></p>
                    <strong><?= (int) $counts['vaccinations_total'] ?></strong>
                </div>

            </div>

            <section id="users" class="welcome-card" style="margin-top: 25px;">
                <h2>Recent users</h2>
                <?php if (!$recent_users): ?>
                    <p>No users are registered.</p>
                <?php else: ?>
                    <div class="table-scroll"><table class="record-table">
                        <thead><tr><th>Name</th><th>Role</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr><td><?= admin_escape($user['full_name']) ?></td><td><?= admin_escape($user['role']) ?></td><td><?= admin_escape($user['status']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
            </section>

            <section id="appointments" class="welcome-card">
                <h2>Recent appointments</h2>
                <p><a href="../staff/appointments/index.php">View all appointments</a></p>
                <?php if (!$recent_appointments): ?>
                    <p>No appointments are recorded.</p>
                <?php else: ?>
                    <div class="table-scroll"><table class="record-table">
                        <thead><tr><th>Date</th><th>Pet</th><th>Owner</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent_appointments as $appointment): ?>
                            <tr>
                                <td><?= admin_escape($appointment['appointment_date'] . ' ' . substr($appointment['appointment_time'], 0, 5)) ?></td>
                                <td><?= admin_escape($appointment['pet_name']) ?></td>
                                <td><?= admin_escape($appointment['owner_name']) ?></td>
                                <td><?= admin_escape($appointment['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
            </section>

            <section id="pets" class="welcome-card">
                <h2>Recent pets</h2>
                <?php if (!$recent_pets): ?>
                    <p>No pets are registered.</p>
                <?php else: ?>
                    <div class="table-scroll"><table class="record-table">
                        <thead><tr><th>Pet</th><th>Species</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent_pets as $pet): ?>
                            <tr><td><?= admin_escape($pet['pet_name']) ?></td><td><?= admin_escape($pet['species']) ?></td><td><?= admin_escape($pet['owner_name']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
            </section>

        </section>

    </main>

</div>

</body>

</html>
