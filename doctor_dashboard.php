<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('doctor');

$doctorStmt = $pdo->prepare('SELECT * FROM doctors WHERE user_id = ? LIMIT 1');
$doctorStmt->execute([current_user_id()]);
$doctor = $doctorStmt->fetch();

if (!$doctor) {
    flash('error', 'Doctor profile not linked. Please contact admin.');
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $status = $_POST['booking_status'] ?? 'pending';
        $update = $pdo->prepare('UPDATE appointments SET booking_status = ? WHERE appointment_id = ? AND doctor_id = ?');
        $update->execute([$status, $appointmentId, $doctor['doctor_id']]);
        flash('success', 'Appointment status updated.');
    } elseif ($action === 'save_record') {
        $diagnosis = clean_input($_POST['diagnosis'] ?? '');
        $prescription = clean_input($_POST['prescription'] ?? '');

        $appointmentStmt = $pdo->prepare('SELECT patient_id, appointment_date FROM appointments WHERE appointment_id = ? AND doctor_id = ?');
        $appointmentStmt->execute([$appointmentId, $doctor['doctor_id']]);
        $appointment = $appointmentStmt->fetch();

        if ($appointment) {
            $recordStmt = $pdo->prepare('INSERT INTO medical_records (patient_id, doctor_id, diagnosis, prescription, visit_date) VALUES (?, ?, ?, ?, ?)');
            $recordStmt->execute([(int) $appointment['patient_id'], $doctor['doctor_id'], $diagnosis, $prescription, $appointment['appointment_date']]);
            $statusUpdate = $pdo->prepare('UPDATE appointments SET booking_status = "completed" WHERE appointment_id = ?');
            $statusUpdate->execute([$appointmentId]);
            flash('success', 'Medical record saved.');
        }
    }

    redirect('doctor_dashboard.php');
}

$todayAppointmentsStmt = $pdo->prepare('SELECT a.*, u.full_name AS patient_name FROM appointments a JOIN users u ON a.patient_id = u.id WHERE a.doctor_id = ? AND a.appointment_date = CURDATE() ORDER BY a.appointment_time ASC');
$todayAppointmentsStmt->execute([$doctor['doctor_id']]);
$todayAppointments = $todayAppointmentsStmt->fetchAll();

$pageTitle = 'Doctor Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Doctor Panel</h2>
        <a href="doctor_dashboard.php">Dashboard</a>
        <a href="available_slots.php?doctor_id=<?php echo (int) $doctor['doctor_id']; ?>">Availability</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header">
            <div>
                <span class="section-tag">Doctor</span>
                <h1><?php echo e($doctor['doctor_name']); ?></h1>
                <p><?php echo e($doctor['specialization']); ?></p>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo count($todayAppointments); ?></strong><span>Today's Appointments</span></div>
            <div class="stat-card"><strong><?php echo e($doctor['available_from']); ?></strong><span>Starts At</span></div>
            <div class="stat-card"><strong><?php echo e($doctor['available_to']); ?></strong><span>Ends At</span></div>
        </div>
        <div class="table-card">
            <h2>Today's Appointments</h2>
            <table>
                <thead><tr><th>Patient</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($todayAppointments as $appointment): ?>
                    <tr>
                        <td><?php echo e($appointment['patient_name']); ?></td>
                        <td><?php echo e(substr((string) $appointment['appointment_time'], 0, 5)); ?></td>
                        <td><span class="badge badge-<?php echo e($appointment['booking_status']); ?>"><?php echo e($appointment['booking_status']); ?></span></td>
                        <td>
                            <form method="post" class="inline-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['appointment_id']; ?>">
                                <select name="booking_status">
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                    <option value="completed">Complete</option>
                                </select>
                                <button class="btn btn-small" type="submit" name="action" value="update_status">Update</button>
                            </form>
                            <form method="post" class="record-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['appointment_id']; ?>">
                                <textarea name="diagnosis" placeholder="Diagnosis" required></textarea>
                                <textarea name="prescription" placeholder="Prescription" required></textarea>
                                <button class="btn btn-secondary btn-small" type="submit" name="action" value="save_record">Save Record</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
