<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$query = clean_input($_GET['query'] ?? '');

$stmt = $pdo->prepare('SELECT doctor_id, doctor_name, specialization, consultation_fee FROM doctors WHERE doctor_name LIKE ? OR specialization LIKE ? ORDER BY doctor_name ASC');
$stmt->execute(['%' . $query . '%', '%' . $query . '%']);

echo json_encode(['success' => true, 'doctors' => $stmt->fetchAll()]);
