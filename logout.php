<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
session_start();
flash('success', 'You have been logged out successfully.');
redirect('index.php');
