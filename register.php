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

    $fullName = clean_input($_POST['full_name'] ?? '');
    $email = strtolower(clean_input($_POST['email'] ?? ''));
    $phone = clean_input($_POST['phone'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($fullName === '' || strlen($fullName) < 3) {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($phone === '' || strlen($phone) < 8) {
        $errors[] = 'Valid phone number is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $insert = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, "patient", NOW())');
            $insert->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $phone]);
            flash('success', 'Registration successful. Please log in.');
            redirect('login.php');
        }
    }
}

$pageTitle = 'Patient Registration';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-copy">
            <span class="section-tag">Register</span>
            <h1>Create your patient account</h1>
            <p>Register to book appointments, view your history, and receive reminders from the hospital system.</p>
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
            <label>Full Name<input type="text" name="full_name" required value="<?php echo e($_POST['full_name'] ?? ''); ?>"></label>
            <label>Email Address<input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"></label>
            <label>Phone Number<input type="text" name="phone" required value="<?php echo e($_POST['phone'] ?? ''); ?>"></label>
            <label>Password<input type="password" name="password" required></label>
            <label>Confirm Password<input type="password" name="confirm_password" required></label>
            <button class="btn btn-primary full-width" type="submit">Create Account</button>
            <p class="form-footer">Already registered? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
