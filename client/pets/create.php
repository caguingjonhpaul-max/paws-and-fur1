<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Pet - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body>

    <h1>Add Pet</h1>

    <form action="create_process.php" method="POST">

        <div class="form-group">

            <label for="pet_name">Pet Name</label>

            <input
                type="text"
                id="pet_name"
                name="pet_name"
                required
            >

        </div>

        <div class="form-group">

            <label for="species">Species</label>

            <select id="species" name="species" required>

                <option value="">Select Species</option>
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Bird">Bird</option>
                <option value="Rabbit">Rabbit</option>
                <option value="Other">Other</option>

            </select>

        </div>

        <div class="form-group">

            <label for="breed">Breed</label>

            <input
                type="text"
                id="breed"
                name="breed"
            >

        </div>

        <div class="form-group">

            <label for="gender">Gender</label>

            <select id="gender" name="gender" required>

                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>

            </select>

        </div>

        <div class="form-group">

            <label for="birth_date">Birth Date</label>

            <input
                type="date"
                id="birth_date"
                name="birth_date"
            >

        </div>

        <div class="form-group">

            <label for="color">Color</label>

            <input
                type="text"
                id="color"
                name="color"
            >

        </div>

        <button type="submit" class="auth-button">
            Add Pet
        </button>

    </form>

    <br>

    <a href="index.php">Back to My Pets</a>

</body>

</html>