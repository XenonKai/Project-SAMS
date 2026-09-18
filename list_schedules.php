<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require 'db.php';

$conn->query(
    "CREATE TABLE IF NOT EXISTS schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject VARCHAR(100) NOT NULL,
        course VARCHAR(100) NOT NULL,
        section VARCHAR(50) NOT NULL,
        schedule_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room VARCHAR(50) NULL,
        faculty VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$result = $conn->query('SELECT id, subject, course, section, schedule_date, start_time, end_time, room, faculty FROM schedules ORDER BY schedule_date ASC, start_time ASC');
$schedules = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

echo json_encode(['success' => true, 'schedules' => $schedules]);
