# Doctor Appointment Booking and Management System

## Requirements

- XAMPP with Apache and MySQL running
- PHP 8+
- phpMyAdmin

## Setup

1. Copy the project folder to `htdocs/appointment`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `database/doctor_booking.sql`.
4. If you want to use the runtime bootstrap data, keep the database name as `doctor_booking_system`.
5. Open `http://localhost/appointment/` in your browser.

## Demo Credentials

- Administrator: `admin@hospital.local` / `Admin@123`
- Doctor: `sarah.khan@hospital.local` / `Doctor@123`

## Notes

- Patients can register from the registration page.
- The system uses session-based login, password hashing, prepared statements, and role-based access control.
- AJAX endpoints are available under `ajax/` for search and slot loading.
