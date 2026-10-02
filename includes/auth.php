<?php

session_start();

function auth($required_role = null)
{
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: /paws_and_fur/auth/login.php");
        exit();
    }

    // Check role if required
    if ($required_role !== null && ($_SESSION['role'] ?? null) !== $required_role) {
        http_response_code(403);
        exit('Access denied.');
        exit();
    }
}

?>
