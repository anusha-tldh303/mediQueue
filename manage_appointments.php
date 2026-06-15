<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('administrator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $status = $_POST['booking_status'] ?? 'pending';
    $stmt = $pdo->prepare('UPDATE appointments SET booking_status = ? WHERE appointment_id = ?');
    $stmt->execute([$status, $appointmentId]);
    flash('success', 'Appointment updated.');
    redirect('manage_appointments.php');
}

$appointments = $pdo->query('SELECT a.*, p.full_name AS patient_name, d.doctor_name FROM appointments a JOIN users p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.doctor_id ORDER BY a.created_at DESC')->fetchAll();
$pageTitle = 'Manage Appointments';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_doctors.php">Manage Doctors</a>
        <a href="manage_patients.php">Manage Patients</a>
        <a href="manage_appointments.php">Manage Appointments</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header"><div><span class="section-tag">Appointments</span><h1>Manage appointments</h1></div></div>
        <div class="table-card">
            <table>
                <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo e($appointment['patient_name']); ?></td>
                        <td><?php echo e($appointment['doctor_name']); ?></td>
                        <td><?php echo e($appointment['appointment_date']); ?></td>
                        <td><?php echo e(substr((string) $appointment['appointment_time'], 0, 5)); ?></td>
                        <td><?php echo e($appointment['booking_status']); ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['appointment_id']; ?>">
                                <select name="booking_status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <button class="btn btn-small" type="submit">Update</button>
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
