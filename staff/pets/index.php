<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/pet_module.php';
auth('Staff');
$result = $conn->query('SELECT p.pet_id, p.pet_name, p.species, p.breed, u.full_name AS owner_name FROM pets p JOIN users u ON u.user_id = p.user_id ORDER BY p.pet_name, p.pet_id');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pet Records - PAWS AND FUR CLINIC</title><link rel="stylesheet" href="../../assets/css/style.css"></head>
<body class="record-page"><main class="record-card">
    <h1>Pet records</h1>
    <?php if ($result->num_rows === 0): ?>
        <p>No pets have been recorded yet.</p>
    <?php else: ?>
        <ul class="record-list">
            <?php while ($pet = $result->fetch_assoc()): ?>
                <li><a href="view.php?id=<?= (int) $pet['pet_id'] ?>"><?= pet_escape($pet['pet_name']) ?></a> — <?= pet_escape($pet['species']) ?><?= $pet['breed'] ? ' / ' . pet_escape($pet['breed']) : '' ?> — Owner: <?= pet_escape($pet['owner_name']) ?></li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
    <p class="record-actions"><a href="../owners/index.php">Pet owners</a><a href="../dashboard.php">Dashboard</a></p>
</main></body></html>
