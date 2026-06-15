<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('administrator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $email = strtolower(clean_input($_POST['email'] ?? ''));
        $phone = clean_input($_POST['phone'] ?? '');
        $password = clean_input($_POST['password'] ?? 'Doctor@123');
        $doctorName = clean_input($_POST['doctor_name'] ?? '');
        $specialization = clean_input($_POST['specialization'] ?? '');
        $qualification = clean_input($_POST['qualification'] ?? '');
        $experience = clean_input($_POST['experience'] ?? '');
        $fee = (int) ($_POST['consultation_fee'] ?? 0);
        $days = clean_input($_POST['available_days'] ?? '');
        $from = clean_input($_POST['available_from'] ?? '09:00:00');
        $to = clean_input($_POST['available_to'] ?? '17:00:00');

        if ($doctorName !== '' && $specialization !== '' && $email !== '') {
            if ($doctorId > 0) {
                if ($userId > 0) {
                    $userUpdate = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ? AND role = "doctor"');
                    $userUpdate->execute([$doctorName, $email, $phone, $userId]);
                }
                $stmt = $pdo->prepare('UPDATE doctors SET doctor_name = ?, specialization = ?, qualification = ?, experience = ?, consultation_fee = ?, available_days = ?, available_from = ?, available_to = ? WHERE doctor_id = ?');
                $stmt->execute([$doctorName, $specialization, $qualification, $experience, $fee, $days, $from, $to, $doctorId]);
            } else {
                $userInsert = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, "doctor", NOW())');
                $userInsert->execute([$doctorName, $email, password_hash($password, PASSWORD_DEFAULT), $phone]);
                $newUserId = (int) $pdo->lastInsertId();

                $stmt = $pdo->prepare('INSERT INTO doctors (user_id, doctor_name, specialization, qualification, experience, consultation_fee, available_days, available_from, available_to, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$newUserId, $doctorName, $specialization, $qualification, $experience, $fee, $days, $from, $to, 'doctor-default.jpg']);
            }
            flash('success', 'Doctor data saved.');
        } else {
            flash('error', 'Doctor name, email, and specialization are required.');
        }
    } elseif ($action === 'delete') {
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $userDelete = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "doctor"');
            $userDelete->execute([$userId]);
        }
        $stmt = $pdo->prepare('DELETE FROM doctors WHERE doctor_id = ?');
        $stmt->execute([$doctorId]);
        flash('success', 'Doctor removed.');
    }
}

$doctors = $pdo->query('SELECT d.*, u.email, u.phone FROM doctors d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.doctor_name ASC')->fetchAll();
$pageTitle = 'Manage Doctors';
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
        <div class="dashboard-header"><div><span class="section-tag">Doctors</span><h1>Manage doctors</h1></div></div>
        <form class="form-card wide-card" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="doctor_id" value="0">
            <input type="hidden" name="user_id" value="0">
            <div class="form-grid">
                <label>Doctor Name<input type="text" name="doctor_name" required></label>
                <label>Email<input type="email" name="email" required></label>
                <label>Phone<input type="text" name="phone" required></label>
                <label>Specialization<input type="text" name="specialization" required></label>
                <label>Qualification<input type="text" name="qualification" required></label>
                <label>Experience<input type="text" name="experience" required></label>
                <label>Consultation Fee<input type="number" name="consultation_fee" min="0" required></label>
                <label>Available Days<input type="text" name="available_days" placeholder="Mon,Tue,Wed" required></label>
                <label>Available From<input type="time" name="available_from" required></label>
                <label>Available To<input type="time" name="available_to" required></label>
                <label>Default Password<input type="text" name="password" value="Doctor@123"></label>
            </div>
            <button class="btn btn-primary" type="submit" name="action" value="save">Add Doctor</button>
        </form>
        <div class="table-card">
            <table>
                <thead><tr><th>Name</th><th>Specialization</th><th>Fee</th><th>Availability</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($doctors as $doctor): ?>
                    <tr>
                        <td><?php echo e($doctor['doctor_name']); ?></td>
                        <td><?php echo e($doctor['specialization']); ?></td>
                        <td>₹<?php echo e($doctor['consultation_fee']); ?></td>
                        <td><?php echo e($doctor['available_days']); ?> | <?php echo e(substr((string) $doctor['available_from'], 0, 5)); ?>-<?php echo e(substr((string) $doctor['available_to'], 0, 5)); ?></td>
                        <td>
                            <details>
                                <summary class="btn btn-small btn-secondary">Edit</summary>
                                <form method="post" class="record-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="doctor_id" value="<?php echo (int) $doctor['doctor_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo (int) ($doctor['user_id'] ?? 0); ?>">
                                    <input type="email" name="email" value="<?php echo e((string) ($doctor['email'] ?? '')); ?>" placeholder="Email" required>
                                    <input type="text" name="phone" value="<?php echo e((string) ($doctor['phone'] ?? '')); ?>" placeholder="Phone" required>
                                    <input type="text" name="doctor_name" value="<?php echo e($doctor['doctor_name']); ?>" required>
                                    <input type="text" name="specialization" value="<?php echo e($doctor['specialization']); ?>" required>
                                    <input type="text" name="qualification" value="<?php echo e($doctor['qualification']); ?>" required>
                                    <input type="text" name="experience" value="<?php echo e($doctor['experience']); ?>" required>
                                    <input type="number" name="consultation_fee" value="<?php echo e((string) $doctor['consultation_fee']); ?>" min="0" required>
                                    <input type="text" name="available_days" value="<?php echo e($doctor['available_days']); ?>" required>
                                    <input type="time" name="available_from" value="<?php echo e(substr((string) $doctor['available_from'], 0, 5)); ?>" required>
                                    <input type="time" name="available_to" value="<?php echo e(substr((string) $doctor['available_to'], 0, 5)); ?>" required>
                                    <button class="btn btn-small" type="submit" name="action" value="save">Update</button>
                                </form>
                            </details>
                            <form method="post" class="inline-form" data-confirm-message="Delete this doctor?">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="doctor_id" value="<?php echo (int) $doctor['doctor_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo (int) ($doctor['user_id'] ?? 0); ?>">
                                <button class="btn btn-danger btn-small" type="submit" name="action" value="delete">Delete</button>
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
