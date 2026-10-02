<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/pet_module.php';
auth('Staff');
$result = $conn->query("SELECT u.full_name, u.email, COUNT(p.pet_id) AS pet_count FROM users u LEFT JOIN pets p ON p.user_id = u.user_id WHERE u.role = 'Client' GROUP BY u.user_id, u.full_name, u.email ORDER BY u.full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pet Owners - PAWS AND FUR CLINIC</title><link rel="stylesheet" href="../../assets/css/style.css"></head>
<body class="record-page"><main class="record-card">
    <h1>Pet owners</h1>
    <?php if ($result->num_rows === 0): ?>
        <p>No pet owners have registered yet.</p>
    <?php else: ?>
        <ul class="record-list">
            <?php while ($owner = $result->fetch_assoc()): ?>
                <li><?= pet_escape($owner['full_name']) ?> — <?= pet_escape($owner['email']) ?> — <?= (int) $owner['pet_count'] ?> pet(s)</li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
    <p class="record-actions"><a href="../pets/index.php">Pet records</a><a href="../dashboard.php">Dashboard</a></p>
</main></body></html>
