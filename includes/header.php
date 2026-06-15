<?php
declare(strict_types=1);

if (!isset($pageTitle)) {
    $pageTitle = 'Doctor Appointment Booking System';
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php">
            <span class="brand-mark">+</span>
            <span>
                <strong>MEDIQUEUE</strong>
                <small>Healthcare Management</small>
            </span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle>Menu</button>
        <nav class="site-nav" data-nav>
            <a href="index.php">Home</a>
            <a href="index.php#doctors">Doctors</a>
            <a href="index.php#services">Services</a>
            <a href="index.php#contact">Contact</a>
            <?php if (is_logged_in()): ?>
                <?php if (current_user_role() === 'patient'): ?>
                    <a href="patient_dashboard.php">Dashboard</a>
                    <a href="book_appointment.php">Book</a>
                    <a href="my_appointments.php">Appointments</a>
                <?php elseif (current_user_role() === 'doctor'): ?>
                    <a href="doctor_dashboard.php">Dashboard</a>
                <?php elseif (current_user_role() === 'administrator'): ?>
                    <a href="admin_dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a class="nav-link-highlight" href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a class="nav-link-highlight" href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <?php if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
        </div>
    <?php endif; ?>
