<?php

function pet_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function pet_csrf_token(): string
{
    if (empty($_SESSION['pet_csrf_token'])) {
        $_SESSION['pet_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['pet_csrf_token'];
}

function pet_check_csrf(): void
{
    if (!hash_equals(pet_csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid form submission. Please go back and try again.');
    }
}

function pet_form_values(array $input): array
{
    return [
        'pet_name' => trim((string) ($input['pet_name'] ?? '')),
        'species' => trim((string) ($input['species'] ?? '')),
        'breed' => trim((string) ($input['breed'] ?? '')),
        'gender' => trim((string) ($input['gender'] ?? '')),
        'birth_date' => trim((string) ($input['birth_date'] ?? '')),
        'color' => trim((string) ($input['color'] ?? '')),
    ];
}

function pet_form_errors(array $pet): array
{
    $errors = [];

    if ($pet['pet_name'] === '' || mb_strlen($pet['pet_name']) > 100) {
        $errors[] = 'Pet name is required and must be at most 100 characters.';
    }
    if (!in_array($pet['species'], ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'], true)) {
        $errors[] = 'Choose a valid species.';
    }
    if (!in_array($pet['gender'], ['Male', 'Female'], true)) {
        $errors[] = 'Choose a valid gender.';
    }
    if (mb_strlen($pet['breed']) > 100 || mb_strlen($pet['color']) > 100) {
        $errors[] = 'Breed and color must each be at most 100 characters.';
    }
    if ($pet['birth_date'] !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $pet['birth_date']);
        if (!$date || $date->format('Y-m-d') !== $pet['birth_date'] || $date > new DateTimeImmutable('today')) {
            $errors[] = 'Enter a valid birth date that is not in the future.';
        }
    }

    return $errors;
}

function pet_flash_form(array $errors, array $values): void
{
    $_SESSION['pet_form_errors'] = $errors;
    $_SESSION['pet_form_values'] = $values;
}

function pet_take_form(): array
{
    $errors = $_SESSION['pet_form_errors'] ?? [];
    $values = $_SESSION['pet_form_values'] ?? [];
    unset($_SESSION['pet_form_errors'], $_SESSION['pet_form_values']);
    return [$errors, $values];
}
