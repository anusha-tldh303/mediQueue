<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$doctorId = (int) ($_GET['doctor_id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

if ($doctorId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

echo json_encode(['success' => true] + get_doctor_slots($pdo, $doctorId, $date));
