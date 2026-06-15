<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('administrator');

$patients = $pdo->query("SELECT * FROM users WHERE role = 'patient' ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Manage Patients';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_doctors.php">Manage Doctors</a>
        <a href="manage_patients.php">Manage Patients</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header"><div><span class="section-tag">Patients</span><h1>Manage patients</h1></div></div>
        <div class="table-card">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?php echo e($patient['full_name']); ?></td>
                        <td><?php echo e($patient['email']); ?></td>
                        <td><?php echo e($patient['phone']); ?></td>
                        <td><?php echo e($patient['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
