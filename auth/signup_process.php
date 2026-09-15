<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit();
}

$full_name = trim($_POST["full_name"]);
$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$confirm_password = $_POST["confirm_password"];

// Check if passwords match
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Check if username already exists
$stmt = $conn->prepare(
    "SELECT user_id FROM users WHERE username = ?"
);

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Username already exists.");
}

$stmt->close();

// Check if email already exists
$stmt = $conn->prepare(
    "SELECT user_id FROM users WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Email is already registered.");
}

$stmt->close();

// Hash password
$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Public registration = Client
$role = "Client";
$status = "Active";

// Insert account
$stmt = $conn->prepare(
    "INSERT INTO users
    (full_name, username, email, password, role, status)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssssss",
    $full_name,
    $username,
    $email,
    $hashed_password,
    $role,
    $status
);

if ($stmt->execute()) {

    echo "
        <script>
            alert('Registration successful!');
            window.location.href = 'login.php';
        </script>
    ";

} else {

    echo "Registration failed.";

}

$stmt->close();
$conn->close();

?>

