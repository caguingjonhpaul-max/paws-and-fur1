<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/appointment_schedule.php';
auth();

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['Client', 'Staff', 'Administrator'], true)) {
    http_response_code(403);
    exit('Access denied.');
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Appointment not found.');
}
$sql = "SELECT a.appointment_id, a.user_id, a.appointment_date, a.appointment_time,
               a.status, a.reason, p.pet_name, u.full_name AS owner_name
        FROM appointments a
        JOIN pets p ON p.pet_id = a.pet_id
        JOIN users u ON u.user_id = a.user_id
        WHERE a.appointment_id = ?";
if ($role === 'Client') {
    $sql .= ' AND a.user_id = ?';
}
$stmt = $conn->prepare($sql);
if ($role === 'Client') {
    $user_id = (int) $_SESSION['user_id'];
    $stmt->bind_param('ii', $id, $user_id);
} else {
    $stmt->bind_param('i', $id);
}
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$appointment) {
    http_response_code(404);
    exit('Appointment not found.');
}
if (!in_array($appointment['status'], ['Pending', 'Approved', 'Confirmed'], true)) {
    http_response_code(409);
    exit('Only active appointments can be rescheduled.');
}

if (isset($_GET['slots'])) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(appointment_schedule_available($conn, (string) ($_GET['date'] ?? ''), $id));
    exit();
}

$return_url = $role === 'Client'
    ? '../client/appointments/index.php'
    : '../staff/appointments/index.php';
$today = appointment_schedule_now()->format('Y-m-d');
$selected_date = $_POST['appointment_date'] ?? ($appointment['appointment_date'] >= $today ? $appointment['appointment_date'] : '');
$selected_time = $_POST['appointment_time'] ?? $appointment['appointment_time'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(appointment_schedule_csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid form submission.');
    }
    $selected_date = trim((string) $selected_date);
    $selected_time = trim((string) $selected_time);
    $date = appointment_schedule_date($selected_date);
    if (!$date || $date < appointment_schedule_now()->setTime(0, 0)) {
        $errors[] = 'Choose a valid date today or later.';
    }
    if (!array_key_exists($selected_time, appointment_schedule_slots())) {
        $errors[] = 'Choose a clinic time slot.';
    }
    if ($selected_date === $appointment['appointment_date']
        && $selected_time === $appointment['appointment_time']) {
        $errors[] = 'Choose a different date or time.';
    }
    if (!$errors) {
        $available = appointment_schedule_available($conn, $selected_date, $id);
        if (!in_array($selected_time, array_column($available, 'value'), true)) {
            $errors[] = 'That time is unavailable. Choose another slot.';
        }
    }
    if (!$errors) {
        if ($role === 'Client') {
            $stmt = $conn->prepare("UPDATE appointments
                SET appointment_date = ?, appointment_time = ?,
                    status = 'Pending', staff_id = NULL
                WHERE appointment_id = ? AND user_id = ?
                  AND status IN ('Pending', 'Approved', 'Confirmed')");
            $stmt->bind_param('ssii', $selected_date, $selected_time, $id, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE appointments
                SET appointment_date = ?, appointment_time = ?
                WHERE appointment_id = ?
                  AND status IN ('Pending', 'Approved', 'Confirmed')");
            $stmt->bind_param('ssi', $selected_date, $selected_time, $id);
        }
        try {
            $saved = $stmt->execute();
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1062) {
                $saved = false;
                $errors[] = 'That time was just booked. Choose another slot.';
            } else {
                throw $exception;
            }
        }
        if ($saved && $stmt->affected_rows === 1) {
            header('Location: ' . $return_url . '?rescheduled=1');
            exit();
        }
        if (!$errors) {
            $errors[] = 'This appointment could not be rescheduled. Refresh and try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule appointment - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card">
    <h1>Reschedule appointment</h1>
    <p><?= appointment_schedule_escape($appointment['pet_name']) ?> — <?= appointment_schedule_escape($appointment['owner_name']) ?></p>
    <p>Current schedule: <strong><?= appointment_schedule_escape($appointment['appointment_date']) ?> at <?= appointment_schedule_escape(substr($appointment['appointment_time'], 0, 5)) ?></strong></p>
    <p>Status: <?= appointment_schedule_escape($appointment['status']) ?></p>
    <?php if ($role === 'Client' && $appointment['status'] !== 'Pending'): ?>
        <p>Your changed appointment will return to Pending for clinic review.</p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="error-message"><?= appointment_schedule_escape($error) ?></p>
    <?php endforeach; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= appointment_schedule_escape(appointment_schedule_csrf_token()) ?>">
        <div class="form-group">
            <label for="appointment_date">New date</label>
            <input id="appointment_date" name="appointment_date" type="date" min="<?= $today ?>" value="<?= appointment_schedule_escape($selected_date) ?>" required>
        </div>
        <div class="form-group">
            <label for="appointment_time">Available time</label>
            <select id="appointment_time" name="appointment_time" required><option value="">Select a date first</option></select>
        </div>
        <button class="auth-button" type="submit">Save new schedule</button>
    </form>
    <p class="record-actions"><a href="<?= $return_url ?>">Back to appointments</a></p>
</main>
<script>
const dateInput = document.getElementById('appointment_date');
const timeSelect = document.getElementById('appointment_time');
const previousTime = <?= json_encode($selected_time, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
async function loadSlots(selected = '') {
    timeSelect.replaceChildren(new Option('Loading available times...', ''));
    if (!dateInput.value) {
        timeSelect.replaceChildren(new Option('Select a date first', ''));
        return;
    }
    try {
        const params = new URLSearchParams({ id: <?= (int) $id ?>, slots: '1', date: dateInput.value });
        const response = await fetch('reschedule.php?' + params.toString(), { credentials: 'same-origin' });
        if (!response.ok) throw new Error('Could not load slots');
        const slots = await response.json();
        timeSelect.replaceChildren(new Option(slots.length ? 'Select available time' : 'No available times', ''));
        for (const slot of slots) {
            const option = new Option(slot.label, slot.value);
            option.selected = slot.value === selected;
            timeSelect.add(option);
        }
    } catch (error) {
        timeSelect.replaceChildren(new Option('Error loading times', ''));
    }
}
dateInput.addEventListener('change', () => loadSlots());
if (dateInput.value) loadSlots(previousTime);
</script>
</body></html>
