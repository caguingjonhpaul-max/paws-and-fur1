<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

auth("Client");

$user_id = $_SESSION['user_id'];


// Get pets belonging to the logged-in client
$stmt = $conn->prepare("
    SELECT pet_id, pet_name, species
    FROM pets
    WHERE user_id = ?
    ORDER BY pet_name ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$pets = $stmt->get_result();


// Available clinic time slots
$time_slots = [
    "09:00:00" => "9:00 AM",
    "10:00:00" => "10:00 AM",
    "11:00:00" => "11:00 AM",
    "13:00:00" => "1:00 PM",
    "14:00:00" => "2:00 PM",
    "15:00:00" => "3:00 PM",
    "16:00:00" => "4:00 PM"
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request Appointment - PAWS AND FUR CLINIC</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body>

    <h1>Request Appointment</h1>

    <form action="create_process.php" method="POST">

        <!-- PET -->

        <div class="form-group">

            <label for="pet_id">
                Select Pet
            </label>

            <select
                id="pet_id"
                name="pet_id"
                required
            >

                <option value="">
                    Select your pet
                </option>

                <?php while ($pet = $pets->fetch_assoc()): ?>

                    <option value="<?= $pet['pet_id'] ?>">

                        <?= htmlspecialchars($pet['pet_name']) ?>
                        -
                        <?= htmlspecialchars($pet['species']) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <!-- DATE -->

        <div class="form-group">

            <label for="appointment_date">
                Appointment Date
            </label>

            <input
                type="date"
                id="appointment_date"
                name="appointment_date"
                min="<?= date('Y-m-d') ?>"
                required
            >

        </div>


        <!-- TIME -->

        <div class="form-group">

            <label for="appointment_time">
                Available Time
            </label>

            <select
                id="appointment_time"
                name="appointment_time"
                required
            >

                <option value="">
                    Select a date first
                </option>

            </select>

        </div>


        <!-- REASON -->

        <div class="form-group">

            <label for="reason">
                Reason for Appointment
            </label>

            <textarea
                id="reason"
                name="reason"
                rows="4"
                placeholder="Enter the reason for your appointment"
                required
            ></textarea>

        </div>


        <button
            type="submit"
            class="auth-button"
        >
            Request Appointment
        </button>

    </form>

    <br>

    <a href="index.php">
        Back to My Appointments
    </a>


<script>

const dateInput = document.getElementById("appointment_date");
const timeSelect = document.getElementById("appointment_time");

dateInput.addEventListener("change", function() {

    const selectedDate = this.value;

    timeSelect.innerHTML = `
        <option value="">
            Loading available times...
        </option>
    `;

    fetch("available_times.php?date=" + selectedDate)

        .then(response => response.json())

        .then(data => {

            timeSelect.innerHTML = "";

            if (data.length === 0) {

                timeSelect.innerHTML = `
                    <option value="">
                        No available times
                    </option>
                `;

                return;
            }


            const defaultOption = document.createElement("option");

            defaultOption.value = "";
            defaultOption.textContent = "Select available time";

            timeSelect.appendChild(defaultOption);


            data.forEach(slot => {

                const option = document.createElement("option");

                option.value = slot.value;
                option.textContent = slot.label;

                timeSelect.appendChild(option);

            });

        })

        .catch(error => {

            console.error(error);

            timeSelect.innerHTML = `
                <option value="">
                    Error loading times
                </option>
            `;

        });

});

</script>

</body>

</html>