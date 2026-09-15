<?php
session_start();
require_once "../config/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h1>PAWS AND FUR</h1>

        <p class="auth-subtitle">
            Veterinary Clinic Management System
        </p>

        <h2>Login</h2>


        <?php if (isset($_SESSION['login_error'])): ?>

            <div class="error-message">

                <?php

                echo $_SESSION['login_error'];

                unset($_SESSION['login_error']);

                ?>

            </div>

        <?php endif; ?>


        <form action="login_process.php" method="POST">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Login
            </button>

        </form>


        <p class="auth-link">

            Don't have an account?

            <a href="signup.php">
                Sign Up
            </a>

        </p>

    </div>

</div>

</body>

</html>