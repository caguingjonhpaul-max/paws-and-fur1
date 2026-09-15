<?php
require_once "../config/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h1>PAWS AND FUR</h1>

        <p class="auth-subtitle">
            Veterinary Clinic Management System
        </p>

        <h2>Create Account</h2>

        <form action="signup_process.php" method="POST">

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >
            </div>

            <button type="submit" class="auth-button">
                Register
            </button>

        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>
</html>

