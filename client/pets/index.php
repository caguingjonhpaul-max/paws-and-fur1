<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";
require_once "../../includes/pet_module.php";

auth("Client");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT pet_id, pet_name, species, breed, gender, birth_date, color
    FROM pets
    WHERE user_id = ?
    ORDER BY pet_name ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Pets - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="record-page">
<main class="record-card">
    <span class="eyebrow">Client portal</span>
    <h1>My pets</h1>
    <p>Manage your pets' information in one place.</p>
    <p class="record-actions">
        <a class="button" href="create.php">Add pet</a>
        <a href="../owner/index.php">Owner information</a>
        <a href="../dashboard.php">Dashboard</a>
    </p>

    <?php if ($result->num_rows > 0): ?>
        <div class="pet-grid">
        <?php while ($pet = $result->fetch_assoc()): ?>

            <article class="pet-card">

                <h2>
                    <?= htmlspecialchars($pet['pet_name']) ?>
                </h2>

                <p>
                    Species:
                    <?= htmlspecialchars($pet['species']) ?>
                </p>

                <p>
                    Breed:
                    <?= htmlspecialchars($pet['breed'] ?? 'N/A') ?>
                </p>

                <p>
                    Gender:
                    <?= htmlspecialchars($pet['gender']) ?>
                </p>

                <p>
                    Birth Date:
                    <?= htmlspecialchars($pet['birth_date'] ?? 'N/A') ?>
                </p>

                <p>
                    Color:
                    <?= htmlspecialchars($pet['color'] ?? 'N/A') ?>
                </p>

                <p class="record-actions">
                    <a href="view.php?id=<?= (int) $pet['pet_id'] ?>">View details</a>
                    <a href="edit.php?id=<?= (int) $pet['pet_id'] ?>">Edit</a>
                </p>
                <form action="delete.php" method="post" onsubmit="return confirm('Are you sure you want to delete this pet?');">
                    <input type="hidden" name="pet_id" value="<?= (int) $pet['pet_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= pet_escape(pet_csrf_token()) ?>">
                    <button type="submit">Delete</button>
                </form>

            </article>

        <?php endwhile; ?>
        </div>

    <?php else: ?>

        <p>You don't have any pets registered yet.</p>

    <?php endif; ?>

</main>
</body>

</html>
