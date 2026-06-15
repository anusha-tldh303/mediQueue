CREATE DATABASE IF NOT EXISTS doctor_booking_system;
USE doctor_booking_system;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS medical_records;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('patient', 'doctor', 'administrator') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    doctor_name VARCHAR(150) NOT NULL,
    specialization VARCHAR(120) NOT NULL,
    qualification VARCHAR(255) NOT NULL,
    experience VARCHAR(100) NOT NULL,
    consultation_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    available_days VARCHAR(100) NOT NULL,
    available_from TIME NOT NULL,
    available_to TIME NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    booking_status ENUM('pending', 'approved', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    queue_number INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uniq_doctor_slot (doctor_id, appointment_date, appointment_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medical_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    prescription TEXT NOT NULL,
    visit_date DATE NOT NULL,
    CONSTRAINT fk_records_patient FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_records_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    status ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (full_name, email, password, phone, role) VALUES
('System Administrator', 'admin@hospital.local', '$2y$10$k0VKwcMBH4ZtAoDIlrdkA.MnJ9b.tiryLdJKOqUaLHw82eaXASate', '9999999999', 'administrator'),
('Dr. Sarah Khan', 'sarah.khan@hospital.local', '$2y$10$kjUo2URIct83zjd1QYWTIOBT.U6hIVGGR2rJPNd.vwn6FxuJExJzS', '9000011111', 'doctor'),
('Dr. Amit Verma', 'amit.verma@hospital.local', '$2y$10$kjUo2URIct83zjd1QYWTIOBT.U6hIVGGR2rJPNd.vwn6FxuJExJzS', '9000022222', 'doctor');

INSERT INTO doctors (user_id, doctor_name, specialization, qualification, experience, consultation_fee, available_days, available_from, available_to, profile_image) VALUES
(2, 'Dr. Sarah Khan', 'Cardiology', 'MBBS, MD', '12 Years', 800, 'Mon,Tue,Wed,Thu,Sat', '09:00:00', '13:00:00', 'doctor1.jpg'),
(3, 'Dr. Amit Verma', 'Dermatology', 'MBBS, DDVL', '8 Years', 600, 'Mon,Wed,Fri,Sat', '10:00:00', '14:00:00', 'doctor2.jpg');
