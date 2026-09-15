<?php

require_once "auth.php";

if ($_SESSION['role'] !== 'Administrator') {
    header("Location: ../dashboard.php");
    exit();
}

?>