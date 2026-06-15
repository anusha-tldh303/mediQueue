<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('patient');

$patientId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $action = $_POST['action'] ?? '';
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);

    $owned = $pdo->prepare('SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ?');
    $owned->execute([$appointmentId, $patientId]);
    $appointment = $owned->fetch();

    if ($appointment) {
        if ($action === 'cancel') {
            $update = $pdo->prepare('UPDATE appointments SET booking_status = "cancelled" WHERE appointment_id = ?');
            $update->execute([$appointmentId]);
            flash('success', 'Appointment cancelled.');
        } elseif ($action === 'reschedule') {
            $newDate = clean_input($_POST['new_date'] ?? '');
            $newTime = clean_input($_POST['new_time'] ?? '');
            $slotInfo = get_doctor_slots($pdo, (int) $appointment['doctor_id'], $newDate);

            if (in_array($newTime, $slotInfo['slots'], true)) {
                $update = $pdo->prepare('UPDATE appointments SET appointment_date = ?, appointment_time = ?, booking_status = "pending" WHERE appointment_id = ?');
                $update->execute([$newDate, $newTime, $appointmentId]);
                flash('success', 'Appointment rescheduled.');
            } else {
                flash('error', 'Selected reschedule slot is not available.');
            }
        }
    }

    redirect('my_appointments.php');
}

$stmt = $pdo->prepare('SELECT a.*, d.doctor_name, d.specialization FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC');
$stmt->execute([$patientId]);
$appointments = $stmt->fetchAll();

$pageTitle = 'My Appointments';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Patient Panel</h2>
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="available_slots.php">Available Slots</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header"><div><span class="section-tag">History</span><h1>Appointment history</h1></div></div>
        <div class="table-card">
            <table>
                <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo e($appointment['doctor_name']); ?> <small><?php echo e($appointment['specialization']); ?></small></td>
                        <td><?php echo e($appointment['appointment_date']); ?></td>
                        <td><?php echo e(substr((string) $appointment['appointment_time'], 0, 5)); ?></td>
                        <td><span class="badge badge-<?php echo e($appointment['booking_status']); ?>"><?php echo e($appointment['booking_status']); ?></span></td>
                        <td>
                            <form method="post" class="inline-form" data-confirm-message="Cancel this appointment?">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['appointment_id']; ?>">
                                <button type="submit" name="action" value="cancel" class="btn btn-danger btn-small">Cancel</button>
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
