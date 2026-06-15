<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('patient');

$patientId = current_user_id();

$statsStmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE patient_id = ?');
$statsStmt->execute([$patientId]);
$totalAppointments = (int) $statsStmt->fetchColumn();

$upcomingStmt = $pdo->prepare('SELECT a.*, d.doctor_name, d.specialization FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() ORDER BY a.appointment_date, a.appointment_time');
$upcomingStmt->execute([$patientId]);
$appointments = $upcomingStmt->fetchAll();

$pageTitle = 'Patient Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Patient Panel</h2>
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="my_appointments.php">My Appointments</a>
        <a href="available_slots.php">Available Slots</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header">
            <div>
                <span class="section-tag">Welcome</span>
                <h1>Hello, <?php echo e(current_user_name()); ?></h1>
                <p>Manage your appointments and view your visit history.</p>
            </div>
            <a class="btn btn-primary" href="book_appointment.php">Book Now</a>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo $totalAppointments; ?></strong><span>Total Appointments</span></div>
            <div class="stat-card"><strong><?php echo count($appointments); ?></strong><span>Upcoming</span></div>
            <div class="stat-card"><strong>Secure</strong><span>Account Access</span></div>
        </div>
        <div class="table-card">
            <h2>Upcoming Appointments</h2>
            <table>
                <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Queue</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo e($appointment['doctor_name']); ?> <small><?php echo e($appointment['specialization']); ?></small></td>
                        <td><?php echo e($appointment['appointment_date']); ?></td>
                        <td><?php echo e(substr((string) $appointment['appointment_time'], 0, 5)); ?></td>
                        <td><span class="badge badge-<?php echo e($appointment['booking_status']); ?>"><?php echo e($appointment['booking_status']); ?></span></td>
                        <td>#<?php echo (int) ($appointment['queue_number'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
