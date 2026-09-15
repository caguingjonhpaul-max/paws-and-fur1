<?php

require_once "auth.php";

if ($_SESSION['role'] !== 'Staff') {
    header("Location: ../dashboard.php");
    exit();
}

?>