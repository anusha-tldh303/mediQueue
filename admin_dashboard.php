<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('administrator');

$stats = [
    'doctors' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn(),
    'patients' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
    'appointments' => (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
];

$daily = (int) $pdo->query('SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()')->fetchColumn();
$monthly = (int) $pdo->query('SELECT COUNT(*) FROM appointments WHERE YEAR(appointment_date) = YEAR(CURDATE()) AND MONTH(appointment_date) = MONTH(CURDATE())')->fetchColumn();
$popularDoctor = $pdo->query('SELECT d.doctor_name, COUNT(a.appointment_id) AS total FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id GROUP BY d.doctor_id ORDER BY total DESC LIMIT 1')->fetch();

$pageTitle = 'Admin Dashboard';
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
        <div class="dashboard-header">
            <div>
                <span class="section-tag">Administration</span>
                <h1>System statistics</h1>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo $stats['doctors']; ?></strong><span>Total Doctors</span></div>
            <div class="stat-card"><strong><?php echo $stats['patients']; ?></strong><span>Total Patients</span></div>
            <div class="stat-card"><strong><?php echo $stats['appointments']; ?></strong><span>Total Appointments</span></div>
            <div class="stat-card"><strong><?php echo $daily; ?></strong><span>Daily Appointments</span></div>
            <div class="stat-card"><strong><?php echo $monthly; ?></strong><span>Monthly Appointments</span></div>
            <div class="stat-card"><strong><?php echo e($popularDoctor['doctor_name'] ?? 'N/A'); ?></strong><span>Most Booked Doctor</span></div>
        </div>
        <div class="action-grid">
            <a class="service-card" href="manage_doctors.php"><h3>Manage Doctors</h3><p>Add, edit, or remove doctor records and availability.</p></a>
            <a class="service-card" href="manage_patients.php"><h3>Manage Patients</h3><p>Review patient accounts and support records.</p></a>
            <a class="service-card" href="manage_appointments.php"><h3>Manage Appointments</h3><p>Control booking status and schedules.</p></a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
