<?php

session_start();

function auth($required_role = null)
{
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../paws_and_fur/login.php");
        exit();
    }

    // Check role if required
    if ($required_role !== null && $_SESSION['role'] !== $required_role) {
        header("Location: ../paws_and_fur/dashboard.php");
        exit();
    }
}

?>