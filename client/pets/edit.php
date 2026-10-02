<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth('Client');

$pet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pet_id) {
    http_response_code(404);
    exit('Pet not found.');
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT pet_name, species, breed, gender, birth_date, color FROM pets WHERE pet_id = ? AND user_id = ?');
$stmt->bind_param('ii', $pet_id, $user_id);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$pet) {
    http_response_code(404);
    exit('Pet not found.');
}

[$errors, $values] = pet_take_form();
$pet = array_merge($pet, $values);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= pet_escape($pet['pet_name']) ?> - PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="record-page">
    <main class="record-card">
        <h1>Edit pet information</h1>
        <?php foreach ($errors as $error): ?>
            <p class="error-message"><?= pet_escape($error) ?></p>
        <?php endforeach; ?>
        <form action="update_process.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= pet_escape(pet_csrf_token()) ?>">
            <input type="hidden" name="pet_id" value="<?= $pet_id ?>">
            <div class="form-group"><label for="pet_name">Pet name</label><input id="pet_name" name="pet_name" required maxlength="100" value="<?= pet_escape($pet['pet_name']) ?>"></div>
            <div class="form-group"><label for="species">Species</label><select id="species" name="species" required>
                <?php foreach (['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'] as $species): ?>
                    <option value="<?= $species ?>" <?= $pet['species'] === $species ? 'selected' : '' ?>><?= $species ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="form-group"><label for="breed">Breed</label><input id="breed" name="breed" maxlength="100" value="<?= pet_escape($pet['breed']) ?>"></div>
            <div class="form-group"><label for="gender">Gender</label><select id="gender" name="gender" required>
                <?php foreach (['Male', 'Female'] as $gender): ?>
                    <option value="<?= $gender ?>" <?= $pet['gender'] === $gender ? 'selected' : '' ?>><?= $gender ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="form-group"><label for="birth_date">Birth date</label><input type="date" id="birth_date" name="birth_date" max="<?= date('Y-m-d') ?>" value="<?= pet_escape($pet['birth_date']) ?>"></div>
            <div class="form-group"><label for="color">Color</label><input id="color" name="color" maxlength="100" value="<?= pet_escape($pet['color']) ?>"></div>
            <button type="submit" class="auth-button">Save changes</button>
        </form>
        <p class="record-actions"><a href="view.php?id=<?= $pet_id ?>">Cancel</a></p>
    </main>
</body>
</html>
