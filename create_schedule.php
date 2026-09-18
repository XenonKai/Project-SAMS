<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require 'db.php';

$subject = trim($_POST['subject'] ?? '');
$course = trim($_POST['course'] ?? '');
$section = trim($_POST['section'] ?? '');
$schedule_date = trim($_POST['schedule_date'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$end_time = trim($_POST['end_time'] ?? '');
$room = trim($_POST['room'] ?? '');
$faculty = trim($_POST['faculty'] ?? '');

if ($subject === '' || $course === '' || $section === '' || $schedule_date === '' || $start_time === '' || $end_time === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Subject, course, section, date, start time, and end time are required.']);
    exit();
}

if ($end_time <= $start_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'End time must be later than start time.']);
    exit();
}

$create = $conn->query(
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

if (!$create) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare the schedules table.']);
    exit();
}

$insert = $conn->prepare(
    'INSERT INTO schedules (subject, course, section, schedule_date, start_time, end_time, room, faculty) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$insert->bind_param('ssssssss', $subject, $course, $section, $schedule_date, $start_time, $end_time, $room, $faculty);

if (!$insert->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save the schedule.']);
    exit();
}

echo json_encode(['success' => true, 'message' => 'Schedule created successfully.', 'id' => $insert->insert_id]);
