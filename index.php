<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$pageTitle = 'Doctor Appointment Booking and Management System';

$featuredDoctors = $pdo->query('SELECT * FROM doctors ORDER BY doctor_id ASC LIMIT 6')->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Mediqueue</span>
            <h2> Skip the Queue Book Online.</h2>
            <p>Manage patient visits, doctor schedules, appointment queues, and medical records from a secure, responsive hospital system built for academic and real-world use.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="register.php">Create Account</a>
                <a class="btn btn-secondary" href="login.php">Login</a>
            </div>
            <div class="hero-stats">
                <div><strong>24/7</strong><span>Access</span></div>
                <div><strong>Smart</strong><span>Scheduling</span></div>
                <div><strong>Secure</strong><span>Sessions</span></div>
            </div>
        </div>
        <div class="hero-card">
            <div class="banner-card">
                <span>Hospital Banner</span>
                <h2>Trusted digital appointment management</h2>
                <p>From registration to follow-up records, every interaction is organized in one place.</p>
            </div>
            <div class="mini-grid">
                <div>Online booking</div>
                <div>Queue numbers</div>
                <div>Doctor alerts</div>
                <div>Medical history</div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section">
    <div class="container two-col">
        <div>
            <span class="section-tag">About</span>
            <h2>Built for hospital workflows and final-year project evaluation.</h2>
        </div>
        <p>The system supports patients, doctors, and administrators with role-based access, prepared statements, hashed passwords, appointment validation, and dashboard analytics.</p>
    </div>
</section>

<section id="doctors" class="section alt-section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="section-tag">Featured Doctors</span>
                <h2>Search specialists and book the nearest available slot.</h2>
            </div>
            <div class="search-bar">
                <input type="text" id="doctorSearch" placeholder="Search by name or specialization" data-doctor-search>
            </div>
        </div>
        <div class="doctor-grid" id="doctorResults">
            <?php foreach ($featuredDoctors as $doctor): ?>
                <article class="doctor-card">
                    <div class="doctor-avatar"><?php echo strtoupper(substr((string) $doctor['doctor_name'], 0, 1)); ?></div>
                    <h3><?php echo e($doctor['doctor_name']); ?></h3>
                    <p><?php echo e($doctor['specialization']); ?></p>
                    <ul>
                        <li><?php echo e($doctor['qualification']); ?></li>
                        <li><?php echo e($doctor['experience']); ?></li>
                        <li>Fee: ₹<?php echo e($doctor['consultation_fee']); ?></li>
                    </ul>
                    <a class="btn btn-small" href="book_appointment.php?doctor_id=<?php echo (int) $doctor['doctor_id']; ?>">Book Now</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="services" class="section">
    <div class="container">
        <span class="section-tag">Services</span>
        <div class="service-grid">
            <article class="service-card"><h3>Appointment Booking</h3><p>Choose doctors, dates, and available time slots in real time.</p></article>
            <article class="service-card"><h3>Doctor Management</h3><p>Administrators can manage doctor availability and records.</p></article>
            <article class="service-card"><h3>Medical History</h3><p>Doctors can record diagnosis and prescriptions after visits.</p></article>
            <article class="service-card"><h3>Reminder Notifications</h3><p>Upcoming appointments generate notification records automatically.</p></article>
        </div>
    </div>
</section>

<section class="section alt-section">
    <div class="container two-col">
        <div>
            <span class="section-tag">Why This System</span>
            <h2>Responsive, secure, and simple enough for viva demonstrations.</h2>
        </div>
        <div class="feature-list">
            <div>Session-based authentication and access control</div>
            <div>Prepared statements and server-side validation</div>
            <div>Smart nearest-slot suggestion and double-booking protection</div>
            <div>Queue numbers, analytics, and appointment history</div>
        </div>
    </div>
</section>

<section id="contact" class="section contact-section">
    <div class="container contact-grid">
        <div>
            <span class="section-tag">Contact</span>
            <h2>Need support or deployment help?</h2>
            <p>Use the XAMPP setup instructions in the project README and import the SQL schema into phpMyAdmin.</p>
        </div>
        <div class="contact-card">
            <p>Email: support@hospital.local</p>
            <p>Phone: +91 90000 00000</p>
            <p>Address: Hospital Management Lab</p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
