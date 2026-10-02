<?php

function appointment_schedule_slots(): array
{
    return [
        '09:00:00' => '9:00 AM',
        '10:00:00' => '10:00 AM',
        '11:00:00' => '11:00 AM',
        '13:00:00' => '1:00 PM',
        '14:00:00' => '2:00 PM',
        '15:00:00' => '3:00 PM',
        '16:00:00' => '4:00 PM',
    ];
}

function appointment_schedule_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
}

function appointment_schedule_date(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Manila'));
    return $date && $date->format('Y-m-d') === $value ? $date : null;
}

function appointment_schedule_available(mysqli $conn, string $date, int $exclude_id): array
{
    $selected_date = appointment_schedule_date($date);
    $now = appointment_schedule_now();
    if (!$selected_date || $selected_date < $now->setTime(0, 0)) {
        return [];
    }

    $stmt = $conn->prepare("SELECT appointment_time FROM appointments
        WHERE appointment_date = ? AND appointment_id <> ?
          AND status IN ('Pending', 'Approved', 'Confirmed')");
    $stmt->bind_param('si', $date, $exclude_id);
    $stmt->execute();
    $occupied = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'appointment_time');
    $stmt->close();

    $available = [];
    foreach (appointment_schedule_slots() as $time => $label) {
        $slot = new DateTimeImmutable($date . ' ' . $time, new DateTimeZone('Asia/Manila'));
        if ($slot > $now && !in_array($time, $occupied, true)) {
            $available[] = ['value' => $time, 'label' => $label];
        }
    }
    return $available;
}

function appointment_schedule_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function appointment_schedule_csrf_token(): string
{
    if (empty($_SESSION['appointment_schedule_csrf'])) {
        $_SESSION['appointment_schedule_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['appointment_schedule_csrf'];
}
