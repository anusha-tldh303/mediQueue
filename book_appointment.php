<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_role('patient');

$errors = [];
$slotsPayload = [];

$selectedDoctor = isset($_GET['doctor_id']) ? (int) $_GET['doctor_id'] : (int) ($_POST['doctor_id'] ?? 0);
$selectedDate = $_POST['appointment_date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid security token.';
    }

    $doctorId = (int) ($_POST['doctor_id'] ?? 0);
    $appointmentDate = clean_input($_POST['appointment_date'] ?? '');
    $appointmentTime = clean_input($_POST['appointment_time'] ?? '');

    if ($doctorId <= 0) {
        $errors[] = 'Select a doctor.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDate)) {
        $errors[] = 'Select a valid appointment date.';
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $appointmentTime)) {
        $errors[] = 'Select a valid appointment time.';
    }

    $slotInfo = get_doctor_slots($pdo, $doctorId, $appointmentDate);
    if (!$slotInfo['available']) {
        $errors[] = 'The selected doctor is not available on that date.';
    } elseif (!in_array($appointmentTime, $slotInfo['slots'], true)) {
        $errors[] = 'The selected time slot is not available.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND booking_status IN ("pending", "approved")');
        $check->execute([$doctorId, $appointmentDate, $appointmentTime]);

        if ((int) $check->fetchColumn() > 0) {
            $errors[] = 'This slot is already booked.';
        } else {
            $patientId = current_user_id();
            $queueNumber = generate_queue_number($pdo, $doctorId, $appointmentDate);
            $insert = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, booking_status, queue_number, created_at) VALUES (?, ?, ?, ?, "pending", ?, NOW())');
            $insert->execute([$patientId, $doctorId, $appointmentDate, $appointmentTime, $queueNumber]);

            create_notification($pdo, $patientId, 'Your appointment request has been submitted successfully.');
            flash('success', 'Appointment booked successfully.');
            redirect('my_appointments.php');
        }
    }
}

if ($selectedDoctor > 0) {
    $slotsPayload = get_doctor_slots($pdo, $selectedDoctor, $selectedDate);
}

$doctors = $pdo->query('SELECT doctor_id, doctor_name, specialization, consultation_fee FROM doctors ORDER BY doctor_name ASC')->fetchAll();

$pageTitle = 'Book Appointment';
require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-shell container">
    <aside class="sidebar">
        <h2>Booking</h2>
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="my_appointments.php">My Appointments</a>
        <a href="available_slots.php">Available Slots</a>
    </aside>
    <div class="dashboard-content">
        <div class="dashboard-header"><div><span class="section-tag">Book</span><h1>Schedule your appointment</h1></div></div>

        <form class="form-card wide-card" method="post" id="bookingForm">
            <?php echo csrf_field(); ?>
            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?><div><?php echo e($error); ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="form-grid">
                <label>Doctor
                    <select name="doctor_id" id="doctorSelect" required data-slot-doctor>
                        <option value="">Select doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo (int) $doctor['doctor_id']; ?>" <?php echo $selectedDoctor === (int) $doctor['doctor_id'] ? 'selected' : ''; ?>>
                                <?php echo e($doctor['doctor_name'] . ' - ' . $doctor['specialization']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Date
                    <input type="date" name="appointment_date" id="dateSelect" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($selectedDate); ?>" required data-slot-date>
                </label>
                <label>Time
                    <select name="appointment_time" id="slotSelect" required data-slot-time>
                        <option value="">Select time slot</option>
                        <?php foreach (($slotsPayload['slots'] ?? []) as $slot): ?>
                            <option value="<?php echo e($slot); ?>"><?php echo e($slot); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="hint-box" id="nearestHint">
                <?php if (!empty($slotsPayload['nearest'])): ?>
                    Nearest available slot: <?php echo e($slotsPayload['nearest']); ?>
                <?php else: ?>
                    Choose a doctor and date to see available slots.
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" type="submit">Confirm Booking</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
