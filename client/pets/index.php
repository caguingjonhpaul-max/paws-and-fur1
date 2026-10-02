<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

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

<body>

    <h1>My Pets</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION['full_name']) ?>
    </p>

    <a href="create.php">Add Pet</a>

    <br><br>

    <a href="../dashboard.php">Back to Dashboard</a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($pet = $result->fetch_assoc()): ?>

            <div>

                <h3>
                    <?= htmlspecialchars($pet['pet_name']) ?>
                </h3>

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

                <a href="delete.php?id=<?= $pet['pet_id'] ?>"
                   onclick="return confirm('Are you sure you want to delete this pet?');">
                    Delete
                </a>

            </div>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>You don't have any pets registered yet.</p>

    <?php endif; ?>

</body>

</html>