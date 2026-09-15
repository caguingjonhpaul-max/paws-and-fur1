<?php

require_once "auth.php";

if ($_SESSION['role'] !== 'Client') {
    header("Location: ../dashboard.php");
    exit();
}

?>