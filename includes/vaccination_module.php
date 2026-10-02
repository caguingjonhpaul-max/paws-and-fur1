<?php

function vaccination_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function vaccination_manage_auth(): void
{
    auth();
    if (!in_array($_SESSION['role'] ?? null, ['Staff', 'Administrator'], true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function vaccination_csrf_token(): string
{
    if (empty($_SESSION['vaccination_csrf_token'])) {
        $_SESSION['vaccination_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['vaccination_csrf_token'];
}

function vaccination_check_csrf(): void
{
    if (!hash_equals(vaccination_csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid form submission.');
    }
}

function vaccination_values(array $input): array
{
    return [
        'vaccine_name' => trim((string) ($input['vaccine_name'] ?? '')),
        'administered_on' => trim((string) ($input['administered_on'] ?? '')),
        'next_due_on' => trim((string) ($input['next_due_on'] ?? '')),
        'administered_by' => trim((string) ($input['administered_by'] ?? '')),
        'notes' => trim((string) ($input['notes'] ?? '')),
    ];
}

function vaccination_date(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Manila'));
    return $date && $date->format('Y-m-d') === $value ? $date : null;
}

function vaccination_today(): DateTimeImmutable
{
    return new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
}

function vaccination_errors(array $values): array
{
    $errors = [];
    if ($values['vaccine_name'] === '' || mb_strlen($values['vaccine_name']) > 120) {
        $errors[] = 'Vaccine name is required and must be at most 120 characters.';
    }
    $administered = vaccination_date($values['administered_on']);
    if (!$administered || $administered > vaccination_today()) {
        $errors[] = 'Enter a valid vaccination date that is not in the future.';
    }
    if ($values['next_due_on'] !== '') {
        $due = vaccination_date($values['next_due_on']);
        if (!$due || ($administered && $due <= $administered)) {
            $errors[] = 'The next due date must be after the vaccination date.';
        }
    }
    if (mb_strlen($values['administered_by']) > 150) {
        $errors[] = 'Administered by must be at most 150 characters.';
    }
    if (mb_strlen($values['notes']) > 1000) {
        $errors[] = 'Notes must be at most 1000 characters.';
    }
    return $errors;
}

function vaccination_status(array $record): string
{
    if (!(bool) $record['is_latest']) {
        return 'Earlier dose';
    }
    if ($record['next_due_on'] === null) {
        return 'No next date';
    }
    $today = vaccination_today();
    $due = vaccination_date($record['next_due_on']);
    if ($due < $today) {
        return 'Overdue';
    }
    if ($due <= $today->modify('+30 days')) {
        return 'Due soon';
    }
    return 'Scheduled';
}

function vaccination_records_sql(): string
{
    return "SELECT v.vaccination_id, v.pet_id, v.vaccine_name, v.administered_on,
                   v.next_due_on, v.administered_by, v.notes,
                   p.pet_name, p.species, p.user_id, u.full_name AS owner_name,
                   NOT EXISTS (
                       SELECT 1 FROM vaccinations newer
                       WHERE newer.pet_id = v.pet_id
                         AND newer.vaccine_name = v.vaccine_name
                         AND (newer.administered_on > v.administered_on
                              OR (newer.administered_on = v.administered_on
                                  AND newer.vaccination_id > v.vaccination_id))
                   ) AS is_latest
            FROM vaccinations v
            JOIN pets p ON p.pet_id = v.pet_id
            JOIN users u ON u.user_id = p.user_id";
}
