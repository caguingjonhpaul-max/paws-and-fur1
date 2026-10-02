<?php

require_once "../includes/staff_auth.php";
require_once "../config/database.php";

$pet_count = (int) $conn->query('SELECT COUNT(*) AS total FROM pets')->fetch_assoc()['total'];
$owner_count = (int) $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Client'")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Dashboard - PAWS AND FUR</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="dashboard-layout">

    <aside class="dashboard-sidebar">

        <div class="dashboard-logo">

            <h2>PAWS AND FUR</h2>

            <p>STAFF</p>

        </div>


        <nav>

            <a href="dashboard.php">Dashboard</a>

            <a href="appointments/index.php">Appointments</a>

            <a href="owners/index.php">Pet Owners</a>

            <a href="pets/index.php">Pets</a>

            <a href="#">Vaccinations</a>

            <a href="#">Veterinarians</a>

            <a href="#">Inventory</a>

            <a href="#">Notifications</a>

            <a href="#">Information</a>

            <a href="#">Reports</a>

        </nav>

    </aside>


    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>

                <h1>Staff Dashboard</h1>

                <p>Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></p>

            </div>


            <div class="user-area">

            <span>Staff</span>

            <a href="../auth/logout.php">
                Logout
            </a>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="welcome-card">

                <h2>Welcome to PAWS AND FUR CLINIC</h2>

                <p>
                    Manage appointments, pet information,
                    vaccinations, and inventory.
                </p>

            </div>


            <div class="dashboard-cards">

                <div class="dashboard-card">

                    <h3>Today's Appointments</h3>

                    <p>Appointments scheduled today.</p>

                    <strong>0</strong>

                </div>


                <div class="dashboard-card">

                    <h3>Pet Owners</h3>

                    <p>Registered pet owners.</p>

                    <strong><?= $owner_count ?></strong>

                </div>


                <div class="dashboard-card">

                    <h3>Pets</h3>

                    <p>Registered pets.</p>

                    <strong><?= $pet_count ?></strong>

                </div>


                <div class="dashboard-card">

                    <h3>Low Stock</h3>

                    <p>Inventory items requiring attention.</p>

                    <strong>0</strong>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
