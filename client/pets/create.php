<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth("Client");

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT full_name, email FROM users WHERE user_id = ? AND role = ?');
$role = 'Client';
$stmt->bind_param('is', $user_id, $role);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$owner) {
    http_response_code(404);
    exit('Owner account not found.');
}

[$errors, $values] = pet_take_form();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Pet - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="record-page">
<main class="record-card">

    <h1>Add Pet</h1>

    <p>Owner: <?= pet_escape($owner['full_name']) ?> (<?= pet_escape($owner['email']) ?>)</p>
    <p><a href="../owner/index.php">Update owner information</a></p>

    <?php foreach ($errors as $error): ?>
        <p class="error-message"><?= pet_escape($error) ?></p>
    <?php endforeach; ?>

    <form action="create_process.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= pet_escape(pet_csrf_token()) ?>">

        <div class="form-group">

            <label for="pet_name">Pet Name</label>

            <input
                type="text"
                id="pet_name"
                name="pet_name"
                maxlength="100"
                value="<?= pet_escape($values['pet_name'] ?? '') ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="species">Species</label>

            <select id="species" name="species" required>

                <option value="">Select Species</option>
                <?php foreach (['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'] as $species): ?>
                    <option value="<?= $species ?>" <?= ($values['species'] ?? '') === $species ? 'selected' : '' ?>><?= $species ?></option>
                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label for="breed">Breed</label>

            <input
                type="text"
                id="breed"
                name="breed"
                maxlength="100"
                value="<?= pet_escape($values['breed'] ?? '') ?>"
            >

        </div>

        <div class="form-group">

            <label for="gender">Gender</label>

            <select id="gender" name="gender" required>

                <option value="">Select Gender</option>
                <?php foreach (['Male', 'Female'] as $gender): ?>
                    <option value="<?= $gender ?>" <?= ($values['gender'] ?? '') === $gender ? 'selected' : '' ?>><?= $gender ?></option>
                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label for="birth_date">Birth Date</label>

            <input
                type="date"
                id="birth_date"
                name="birth_date"
                max="<?= date('Y-m-d') ?>"
                value="<?= pet_escape($values['birth_date'] ?? '') ?>"
            >

        </div>

        <div class="form-group">

            <label for="color">Color</label>

            <input
                type="text"
                id="color"
                name="color"
                maxlength="100"
                value="<?= pet_escape($values['color'] ?? '') ?>"
            >

        </div>

        <button type="submit" class="auth-button">
            Add Pet
        </button>

    </form>

    <br>

    <a href="index.php">Back to My Pets</a>

</main>
</body>

</html>
