<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/vaccination_module.php';
vaccination_manage_auth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (isset($_GET['id']) && !$id) {
    http_response_code(404);
    exit('Vaccination record not found.');
}
$record = null;
if ($id) {
    $stmt = $conn->prepare('SELECT v.*, p.pet_name, u.full_name AS owner_name FROM vaccinations v JOIN pets p ON p.pet_id = v.pet_id JOIN users u ON u.user_id = p.user_id WHERE v.vaccination_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$record) {
        http_response_code(404);
        exit('Vaccination record not found.');
    }
}

$pet_id = $record ? (int) $record['pet_id'] : filter_input(INPUT_GET, 'pet_id', FILTER_VALIDATE_INT);
$pets = $conn->query('SELECT p.pet_id, p.pet_name, u.full_name AS owner_name FROM pets p JOIN users u ON u.user_id = p.user_id ORDER BY u.full_name, p.pet_name')->fetch_all(MYSQLI_ASSOC);
$errors = $_SESSION['vaccination_form_errors'] ?? [];
$old = $_SESSION['vaccination_form_values'] ?? [];
unset($_SESSION['vaccination_form_errors'], $_SESSION['vaccination_form_values']);
$values = array_merge($record ?: [], $old);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $record ? 'Edit' : 'Record' ?> Vaccination - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page"><main class="record-card">
    <h1><?= $record ? 'Edit vaccination' : 'Record vaccination' ?></h1>
    <?php foreach ($errors as $error): ?><p class="error-message"><?= vaccination_escape($error) ?></p><?php endforeach; ?>
    <?php if (!$pets): ?>
        <p>No pets are registered yet.</p>
    <?php else: ?>
        <form action="save.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= vaccination_escape(vaccination_csrf_token()) ?>">
            <?php if ($record): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>
            <div class="form-group">
                <label for="pet_id">Pet</label>
                <?php if ($record): ?>
                    <p><?= vaccination_escape($record['pet_name']) ?> — <?= vaccination_escape($record['owner_name']) ?></p>
                <?php else: ?>
                    <select id="pet_id" name="pet_id" required>
                        <option value="">Select a pet</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?= (int) $pet['pet_id'] ?>" <?= (int) ($old['pet_id'] ?? $pet_id) === (int) $pet['pet_id'] ? 'selected' : '' ?>><?= vaccination_escape($pet['owner_name']) ?> — <?= vaccination_escape($pet['pet_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group"><label for="vaccine_name">Vaccine name</label><input id="vaccine_name" name="vaccine_name" maxlength="120" required value="<?= vaccination_escape($values['vaccine_name'] ?? '') ?>"></div>
            <div class="form-group"><label for="administered_on">Date given</label><input id="administered_on" name="administered_on" type="date" max="<?= vaccination_today()->format('Y-m-d') ?>" required value="<?= vaccination_escape($values['administered_on'] ?? '') ?>"></div>
            <div class="form-group"><label for="next_due_on">Next due date (optional)</label><input id="next_due_on" name="next_due_on" type="date" value="<?= vaccination_escape($values['next_due_on'] ?? '') ?>"></div>
            <div class="form-group"><label for="administered_by">Administered by (optional)</label><input id="administered_by" name="administered_by" maxlength="150" value="<?= vaccination_escape($values['administered_by'] ?? '') ?>"></div>
            <div class="form-group"><label for="notes">Notes (optional)</label><textarea id="notes" name="notes" maxlength="1000" rows="4"><?= vaccination_escape($values['notes'] ?? '') ?></textarea></div>
            <button class="auth-button" type="submit">Save vaccination</button>
        </form>
    <?php endif; ?>
    <p class="record-actions"><a href="index.php">Back to vaccination records</a></p>
</main></body></html>
