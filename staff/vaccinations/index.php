<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/vaccination_module.php';
vaccination_manage_auth();

$records = $conn->query(vaccination_records_sql() . '
    ORDER BY v.administered_on DESC, v.vaccination_id DESC')->fetch_all(MYSQLI_ASSOC);
$schedule = [];
foreach ($records as $record) {
    $status = vaccination_status($record);
    if (in_array($status, ['Overdue', 'Due soon', 'Scheduled'], true)) {
        $record['schedule_status'] = $status;
        $schedule[] = $record;
    }
}
usort($schedule, static fn($a, $b) => strcmp($a['next_due_on'], $b['next_due_on']));
$home = ($_SESSION['role'] ?? '') === 'Administrator' ? '../../admin/dashboard.php' : '../dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccinations - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card vaccination-card">
    <span class="eyebrow">Clinic records</span>
    <h1>Vaccination management</h1>
    <p>Record doses and monitor the next date for each pet and vaccine.</p>
    <p class="record-actions">
        <a class="button" href="form.php">Record vaccination</a>
        <a href="<?= $home ?>">Dashboard</a>
    </p>
    <?php if (isset($_GET['saved'])): ?>
        <p class="success-message">Vaccination record saved.</p>
    <?php endif; ?>

    <h2>Vaccination schedule</h2>
    <?php if (!$schedule): ?>
        <p>No upcoming or overdue doses have been recorded.</p>
    <?php else: ?>
        <div class="table-scroll"><table class="record-table">
            <thead><tr><th>Due</th><th>Pet</th><th>Owner</th><th>Vaccine</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($schedule as $record): ?>
                <tr>
                    <td><?= vaccination_escape($record['next_due_on']) ?></td>
                    <td><?= vaccination_escape($record['pet_name']) ?></td>
                    <td><?= vaccination_escape($record['owner_name']) ?></td>
                    <td><a href="form.php?id=<?= (int) $record['vaccination_id'] ?>"><?= vaccination_escape($record['vaccine_name']) ?></a></td>
                    <td><span class="vaccine-status"><?= vaccination_escape($record['schedule_status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>

    <h2>All vaccination records</h2>
    <?php if (!$records): ?>
        <p>No vaccinations have been recorded yet.</p>
    <?php else: ?>
        <div class="table-scroll"><table class="record-table">
            <thead><tr><th>Pet</th><th>Vaccine</th><th>Given</th><th>Next due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= vaccination_escape($record['pet_name']) ?> <small><?= vaccination_escape($record['owner_name']) ?></small></td>
                    <td><?= vaccination_escape($record['vaccine_name']) ?></td>
                    <td><?= vaccination_escape($record['administered_on']) ?></td>
                    <td><?= vaccination_escape($record['next_due_on'] ?: 'Not set') ?></td>
                    <td><span class="vaccine-status"><?= vaccination_escape(vaccination_status($record)) ?></span></td>
                    <td><a href="form.php?id=<?= (int) $record['vaccination_id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</main></body></html>
