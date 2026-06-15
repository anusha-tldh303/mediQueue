<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(match (current_user_role()) {
        'administrator' => 'admin_dashboard.php',
        'doctor' => 'doctor_dashboard.php',
        default => 'patient_dashboard.php',
    });
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid security token.';
    }

    $email = strtolower(clean_input($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, (string) $user['password'])) {
            login_user($user);

            if ($user['role'] === 'administrator') {
                redirect('admin_dashboard.php');
            }
            if ($user['role'] === 'doctor') {
                redirect('doctor_dashboard.php');
            }

            redirect('patient_dashboard.php');
        }

        $errors[] = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-copy">
            <span class="section-tag">Welcome Back</span>
            <h1>Log in to your dashboard</h1>
            <p>Access your appointments, schedules, and admin controls from one secure portal.</p>
        </div>
        <form class="form-card" method="post" novalidate>
            <?php echo csrf_field(); ?>
            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <label>Email Address<input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"></label>
            <label>Password<input type="password" name="password" required></label>
            <button class="btn btn-primary full-width" type="submit">Login</button>
            <p class="form-footer">New patient? <a href="register.php">Register here</a>.</p>
            <div class="demo-box">
                <strong>Demo Logins</strong>
                <p>Admin: admin@hospital.local / Admin@123</p>
                <p>Doctor: sarah.khan@hospital.local / Doctor@123</p>
            </div>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
