CREATE DATABASE IF NOT EXISTS aclc_sams;

USE aclc_sams;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    role VARCHAR(20) NOT NULL,

    full_name VARCHAR(100) NOT NULL,

    phone VARCHAR(20) NULL UNIQUE,

    email VARCHAR(100) NULL UNIQUE,

    password VARCHAR(255) NULL,

    status VARCHAR(20) NOT NULL DEFAULT 'Pending',

    student_id VARCHAR(50) NULL UNIQUE,

    course VARCHAR(50) NULL,

    year_level VARCHAR(20) NULL,

    faculty_id VARCHAR(50) NULL UNIQUE,

    department VARCHAR(100) NULL,

    admin_id VARCHAR(50) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Temporary local admin account for manual login without phpMyAdmin.
-- Phone: 09161196693
-- Password: 1029384756
INSERT INTO users (role, full_name, phone, password, status)
VALUES ('admin', 'Temporary Admin', '09161196693', '1029384756', 'Registered');
