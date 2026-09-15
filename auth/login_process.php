<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Make sure request came from login form
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$username = trim($_POST["username"]);
$password = $_POST["password"];


/*
|--------------------------------------------------------------------------
| Find user
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT user_id, full_name, username, email, password, role, status
     FROM users
     WHERE username = ?
     LIMIT 1"
);

$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Check if user exists
|--------------------------------------------------------------------------
*/

if ($result->num_rows !== 1) {

    $_SESSION['login_error'] = "Invalid username or password.";

    header("Location: login.php");

    exit();

}


$user = $result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| Check account status
|--------------------------------------------------------------------------
*/

if ($user['status'] !== 'Active') {

    $_SESSION['login_error'] =
        "Your account is inactive. Please contact the administrator.";

    header("Location: login.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| Verify password
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user['password'])) {

    $_SESSION['login_error'] = "Invalid username or password.";

    header("Location: login.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| Login successful
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| Store user information in session
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] = $user['user_id'];

$_SESSION['full_name'] = $user['full_name'];

$_SESSION['username'] = $user['username'];

$_SESSION['email'] = $user['email'];

$_SESSION['role'] = $user['role'];


/*
|--------------------------------------------------------------------------
| Redirect based on role
|--------------------------------------------------------------------------
*/

if ($user['role'] === 'Administrator') {

    header("Location: ../admin/dashboard.php");

    exit();

}


if ($user['role'] === 'Staff') {

    header("Location: ../staff/dashboard.php");

    exit();

}


if ($user['role'] === 'Client') {

    header("Location: ../client/dashboard.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| Unknown role
|--------------------------------------------------------------------------
*/

session_destroy();

die("Invalid user role.");

?>