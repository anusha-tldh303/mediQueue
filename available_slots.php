<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$doctorId = isset($_GET['doctor_id']) ? (int) $_GET['doctor_id'] : 0;
$date = $_GET['date'] ?? date('Y-m-d');

$doctors = $pdo->query('SELECT doctor_id, doctor_name, specialization FROM doctors ORDER BY doctor_name ASC')->fetchAll();
$slotInfo = $doctorId > 0 ? get_doctor_slots($pdo, $doctorId, $date) : ['slots' => [], 'nearest' => null, 'available' => false];

$pageTitle = 'Available Slots';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="section-tag">Slots</span><h1>Check available appointment slots</h1></div>
        </div>
        <form class="form-card wide-card" method="get">
            <div class="form-grid">
                <label>Doctor
                    <select name="doctor_id">
                        <option value="">Select doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo (int) $doctor['doctor_id']; ?>" <?php echo $doctorId === (int) $doctor['doctor_id'] ? 'selected' : ''; ?>>
                                <?php echo e($doctor['doctor_name'] . ' - ' . $doctor['specialization']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Date<input type="date" name="date" value="<?php echo e($date); ?>"></label>
            </div>
            <button class="btn btn-primary" type="submit">Check Slots</button>
        </form>
        <div class="slot-results">
            <?php if ($doctorId > 0 && $slotInfo['available']): ?>
                <h2>Available time slots</h2>
                <p>Nearest available slot: <?php echo e($slotInfo['nearest'] ?? 'N/A'); ?></p>
                <div class="slot-grid">
                    <?php foreach ($slotInfo['slots'] as $slot): ?>
                        <div class="slot-chip"><?php echo e($slot); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($doctorId > 0): ?>
                <div class="alert alert-error">No availability found for the selected doctor and date.</div>
            <?php else: ?>
                <p>Select a doctor and date to preview available slots.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
