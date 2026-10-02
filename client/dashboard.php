<?php

require_once "../includes/client_auth.php";
require_once "../config/database.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Client Dashboard - PAWS AND FUR</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="dashboard-layout">

    <aside class="dashboard-sidebar">

        <div class="dashboard-logo">

            <h2>PAWS AND FUR</h2>

            <p>CLIENT</p>

        </div>


        <nav>

            <a href="dashboard.php">Dashboard</a>

            <a href="pets/index.php">My Pets</a>

            <a href="appointments/index.php">Appointments</a>

            <a href="#">Vaccinations</a>

            <a href="#">Notifications</a>

            <a href="#">Clinic Information</a>

        </nav>

    </aside>


    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>

                <h1>Client Dashboard</h1>

                <p>Welcome, Client</p>

            </div>


            <div class="user-area">

                <span>Client</span>

                <a href="../auth/logout.php">
                    Logout
                </a>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="welcome-card">

                <h2>Welcome to PAWS AND FUR CLINIC</h2>

                <p>
                    View your pets, appointments,
                    vaccination records, and notifications.
                </p>

            </div>


            <div class="dashboard-cards">

                <div class="dashboard-card">

                    <h3>My Pets</h3>

                    <p>View your registered pets.</p>

                    <strong>0</strong>

                </div>


                <div class="dashboard-card">

                    <h3>Appointments</h3>

                    <p>Your upcoming appointments.</p>

                    <strong>0</strong>

                </div>


                <div class="dashboard-card">

                    <h3>Vaccinations</h3>

                    <p>View your pets' vaccination records.</p>

                    <strong>0</strong>

                </div>


                <div class="dashboard-card">

                    <h3>Notifications</h3>

                    <p>View clinic notifications.</p>

                    <strong>0</strong>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>