<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

header("Content-Type: application/json");

$date = $_GET['date'] ?? '';

if (empty($date)) {

    echo json_encode([]);

    exit();

}


// Clinic time slots
$time_slots = [
    "09:00:00" => "9:00 AM",
    "10:00:00" => "10:00 AM",
    "11:00:00" => "11:00 AM",
    "13:00:00" => "1:00 PM",
    "14:00:00" => "2:00 PM",
    "15:00:00" => "3:00 PM",
    "16:00:00" => "4:00 PM"
];


// Get occupied slots
$stmt = $conn->prepare("
    SELECT appointment_time
    FROM appointments
    WHERE appointment_date = ?
    AND status IN ('Pending', 'Approved', 'Confirmed')
");

$stmt->bind_param("s", $date);
$stmt->execute();

$result = $stmt->get_result();

$occupied_times = [];

while ($row = $result->fetch_assoc()) {

    $occupied_times[] = $row['appointment_time'];

}


// Create available slots
$available_times = [];

foreach ($time_slots as $value => $label) {

    if (!in_array($value, $occupied_times)) {

        $available_times[] = [
            "value" => $value,
            "label" => $label
        ];

    }

}


echo json_encode($available_times);

?>