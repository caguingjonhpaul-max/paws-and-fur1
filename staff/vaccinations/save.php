<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/vaccination_module.php';
vaccination_manage_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}
vaccination_check_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (isset($_POST['id']) && !$id) {
    http_response_code(400);
    exit('Invalid vaccination record.');
}
$pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
$form_url = 'form.php' . ($id ? '?id=' . $id : ($pet_id ? '?pet_id=' . $pet_id : ''));
$values = vaccination_values($_POST);
$errors = vaccination_errors($values);

if ($id) {
    $stmt = $conn->prepare('SELECT vaccination_id FROM vaccinations WHERE vaccination_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows === 1;
    $stmt->close();
    if (!$exists) {
        http_response_code(404);
        exit('Vaccination record not found.');
    }
} else {
    if (!$pet_id) {
        $errors[] = 'Select a pet.';
    } else {
        $stmt = $conn->prepare('SELECT pet_id FROM pets WHERE pet_id = ?');
        $stmt->bind_param('i', $pet_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows !== 1) {
            $errors[] = 'Selected pet was not found.';
        }
        $stmt->close();
    }
}
if ($errors) {
    $_SESSION['vaccination_form_errors'] = $errors;
    $_SESSION['vaccination_form_values'] = $values + ['pet_id' => $pet_id];
    header('Location: ' . $form_url);
    exit();
}

$due = $values['next_due_on'] ?: null;
$by = $values['administered_by'] ?: null;
$notes = $values['notes'] ?: null;
if ($id) {
    $stmt = $conn->prepare('UPDATE vaccinations SET vaccine_name = ?, administered_on = ?, next_due_on = ?, administered_by = ?, notes = ? WHERE vaccination_id = ?');
    $stmt->bind_param('sssssi', $values['vaccine_name'], $values['administered_on'], $due, $by, $notes, $id);
} else {
    $recorder = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO vaccinations (pet_id, vaccine_name, administered_on, next_due_on, administered_by, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssssi', $pet_id, $values['vaccine_name'], $values['administered_on'], $due, $by, $notes, $recorder);
}
try {
    $saved = $stmt->execute();
} catch (mysqli_sql_exception $exception) {
    $saved = false;
}
$stmt->close();
if (!$saved) {
    $_SESSION['vaccination_form_errors'] = ['Unable to save the vaccination. Please try again.'];
    $_SESSION['vaccination_form_values'] = $values + ['pet_id' => $pet_id];
    header('Location: ' . $form_url);
    exit();
}
header('Location: index.php?saved=1');
exit();
