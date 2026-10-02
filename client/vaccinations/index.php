<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/vaccination_module.php';
auth('Client');
$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare(vaccination_records_sql() . '
    WHERE p.user_id = ?
    ORDER BY v.administered_on DESC, v.vaccination_id DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$schedule = [];
foreach ($records as $record) {
    $status = vaccination_status($record);
    if (in_array($status, ['Overdue', 'Due soon', 'Scheduled'], true)) {
        $record['schedule_status'] = $status;
        $schedule[] = $record;
    }
}
usort($schedule, static fn($a, $b) => strcmp($a['next_due_on'], $b['next_due_on']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets' Vaccinations - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card vaccination-card">
    <span class="eyebrow">Client portal</span>
    <h1>My pets' vaccinations</h1>
    <p>Vaccination records entered by the clinic and their next due dates.</p>
    <p class="record-actions"><a href="../pets/index.php">My pets</a><a href="../dashboard.php">Dashboard</a></p>
    <h2>Upcoming and overdue</h2>
    <?php if (!$schedule): ?>
        <p>No upcoming or overdue vaccinations are recorded.</p>
    <?php else: ?>
        <div class="table-scroll"><table class="record-table">
            <thead><tr><th>Due</th><th>Pet</th><th>Vaccine</th><th>Status</th></tr></thead>
            <tbody><?php foreach ($schedule as $record): ?>
                <tr><td><?= vaccination_escape($record['next_due_on']) ?></td><td><?= vaccination_escape($record['pet_name']) ?></td><td><?= vaccination_escape($record['vaccine_name']) ?></td><td><span class="vaccine-status"><?= vaccination_escape($record['schedule_status']) ?></span></td></tr>
            <?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
    <h2>Vaccination history</h2>
    <?php if (!$records): ?>
        <p>No vaccination records have been entered for your pets yet.</p>
    <?php else: ?>
        <div class="table-scroll"><table class="record-table">
            <thead><tr><th>Pet</th><th>Vaccine</th><th>Given</th><th>Next due</th><th>Administered by</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody><?php foreach ($records as $record): ?>
                <tr>
                    <td><?= vaccination_escape($record['pet_name']) ?></td>
                    <td><?= vaccination_escape($record['vaccine_name']) ?></td>
                    <td><?= vaccination_escape($record['administered_on']) ?></td>
                    <td><?= vaccination_escape($record['next_due_on'] ?: 'Not set') ?></td>
                    <td><?= vaccination_escape($record['administered_by'] ?: 'Not set') ?></td>
                    <td><span class="vaccine-status"><?= vaccination_escape(vaccination_status($record)) ?></span></td>
                    <td><?= vaccination_escape($record['notes'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</main></body></html>
