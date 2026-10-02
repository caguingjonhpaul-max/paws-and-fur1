<?php

require_once "../includes/admin_auth.php";
require_once "../config/database.php";

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

            <a href="#">User Management</a>

            <a href="#">Appointments</a>

            <a href="#">Pet Owners</a>

            <a href="#">Pets</a>

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
                    Manage users, appointments, pets, veterinarians,
                    inventory, and clinic information from this dashboard.
                </p>

            </div>


            <div class="dashboard-cards">

                <div class="dashboard-card">
                    <h3>Users</h3>
                    <p>Manage system users and their accounts.</p>
                    <strong>0</strong>
                </div>


                <div class="dashboard-card">
                    <h3>Appointments</h3>
                    <p>View and manage clinic appointments.</p>
                    <strong>0</strong>
                </div>


                <div class="dashboard-card">
                    <h3>Pets</h3>
                    <p>View registered pets.</p>
                    <strong>0</strong>
                </div>


                <div class="dashboard-card">
                    <h3>Inventory</h3>
                    <p>Monitor available supplies.</p>
                    <strong>0</strong>
                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
