<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}

function clean_input(?string $value): string
{
    return trim((string) $value);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function current_user_name(): string
{
    return $_SESSION['full_name'] ?? 'Guest';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function has_role(string|array $roles): bool
{
    $role = current_user_role();

    if ($role === null) {
        return false;
    }

    $allowed = is_array($roles) ? $roles : [$roles];

    return in_array($role, $allowed, true);
}

function appointment_time_slots(string $startTime, string $endTime, int $intervalMinutes = 30): array
{
    $slots = [];
    $start = DateTime::createFromFormat('H:i:s', $startTime) ?: DateTime::createFromFormat('H:i', $startTime);
    $end = DateTime::createFromFormat('H:i:s', $endTime) ?: DateTime::createFromFormat('H:i', $endTime);

    if (!$start || !$end) {
        return $slots;
    }

    while ($start < $end) {
        $slots[] = $start->format('H:i');
        $start->modify('+' . $intervalMinutes . ' minutes');
    }

    return $slots;
}

function normalize_available_days(string $days): array
{
    return array_values(array_filter(array_map('trim', explode(',', $days))));
}

function doctor_is_available_on_date(array $doctor, string $date): bool
{
    $dayCode = date('D', strtotime($date));
    $availableDays = normalize_available_days($doctor['available_days'] ?? '');

    return in_array($dayCode, $availableDays, true);
}

function get_doctor_slots(PDO $pdo, int $doctorId, string $date): array
{
    $doctorStmt = $pdo->prepare('SELECT * FROM doctors WHERE doctor_id = ?');
    $doctorStmt->execute([$doctorId]);
    $doctor = $doctorStmt->fetch();

    if (!$doctor) {
        return ['slots' => [], 'nearest' => null, 'available' => false];
    }

    if (!doctor_is_available_on_date($doctor, $date)) {
        return ['slots' => [], 'nearest' => null, 'available' => false];
    }

    $allSlots = appointment_time_slots((string) $doctor['available_from'], (string) $doctor['available_to']);

    $appointmentStmt = $pdo->prepare(
        'SELECT appointment_time FROM appointments
         WHERE doctor_id = ? AND appointment_date = ? AND booking_status IN ("pending", "approved")'
    );
    $appointmentStmt->execute([$doctorId, $date]);
    $booked = array_map(static fn (array $row): string => substr((string) $row['appointment_time'], 0, 5), $appointmentStmt->fetchAll());

    $today = date('Y-m-d');
    $nowTime = date('H:i');

    $slots = [];
    foreach ($allSlots as $slot) {
        if ($date === $today && $slot <= $nowTime) {
            continue;
        }

        if (!in_array($slot, $booked, true)) {
            $slots[] = $slot;
        }
    }

    $nearest = $slots[0] ?? null;

    return ['slots' => $slots, 'nearest' => $nearest, 'available' => true];
}

function generate_queue_number(PDO $pdo, int $doctorId, string $date): int
{
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(queue_number), 0) + 1 AS next_queue FROM appointments WHERE doctor_id = ? AND appointment_date = ?');
    $stmt->execute([$doctorId, $date]);

    return (int) $stmt->fetchColumn();
}

function create_notification(PDO $pdo, int $userId, string $message): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, "unread", NOW())');
    $stmt->execute([$userId, $message]);
}

function mark_notifications_read(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('UPDATE notifications SET status = "read" WHERE user_id = ?');
    $stmt->execute([$userId]);
}

function bootstrap_default_data(PDO $pdo): void
{
    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($userCount > 0) {
        return;
    }

    $pdo->beginTransaction();

    try {
        $insertUser = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insertDoctor = $pdo->prepare('INSERT INTO doctors (user_id, doctor_name, specialization, qualification, experience, consultation_fee, available_days, available_from, available_to, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $adminPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
        $insertUser->execute(['System Administrator', 'admin@hospital.local', $adminPassword, '9999999999', 'administrator']);

        $doctorOnePassword = password_hash('Doctor@123', PASSWORD_DEFAULT);
        $insertUser->execute(['Dr. Sarah Khan', 'sarah.khan@hospital.local', $doctorOnePassword, '9000011111', 'doctor']);
        $doctorOneUserId = (int) $pdo->lastInsertId();
        $insertDoctor->execute([$doctorOneUserId, 'Dr. Sarah Khan', 'Cardiology', 'MBBS, MD', '12 Years', '800', 'Mon,Tue,Wed,Thu,Sat', '09:00:00', '13:00:00', 'doctor1.jpg']);

        $doctorTwoPassword = password_hash('Doctor@123', PASSWORD_DEFAULT);
        $insertUser->execute(['Dr. Amit Verma', 'amit.verma@hospital.local', $doctorTwoPassword, '9000022222', 'doctor']);
        $doctorTwoUserId = (int) $pdo->lastInsertId();
        $insertDoctor->execute([$doctorTwoUserId, 'Dr. Amit Verma', 'Dermatology', 'MBBS, DDVL', '8 Years', '600', 'Mon,Wed,Fri,Sat', '10:00:00', '14:00:00', 'doctor2.jpg']);

        $pdo->commit();
    } catch (Throwable $throwable) {
        $pdo->rollBack();
    }
}
