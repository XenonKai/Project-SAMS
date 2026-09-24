-- Attendance storage used by the student dashboard.
-- The PHP pages also create this table automatically for existing installations.
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    schedule_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','late','absent','excused') NOT NULL DEFAULT 'present',
    time_in DATETIME NULL,
    time_out DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY student_schedule_date (student_id, schedule_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
