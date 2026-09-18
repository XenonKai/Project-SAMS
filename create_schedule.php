<?php
session_start();

// Keep API responses JSON even when PHP/MySQL reports an error.
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

function jsonResponse(bool $success, string $message, int $status = 200, array $extra = []): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Your admin session has expired. Please log in again.', 403);
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
    jsonResponse(false, 'Subject, course, section, date, start time, and end time are required.', 400);
}

$date = DateTime::createFromFormat('Y-m-d', $schedule_date);
if (!$date || $date->format('Y-m-d') !== $schedule_date) {
    jsonResponse(false, 'Please enter a valid schedule date.', 400);
}

if ($end_time <= $start_time) {
    jsonResponse(false, 'End time must be later than start time.', 400);
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
    error_log('Schedule table error: ' . $conn->error);
    jsonResponse(false, 'The schedules table could not be prepared. Check your database permissions.', 500);
}

$insert = $conn->prepare(
    'INSERT INTO schedules (subject, course, section, schedule_date, start_time, end_time, room, faculty) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$insert) {
    error_log('Schedule prepare error: ' . $conn->error);
    jsonResponse(false, 'The schedule query could not be prepared.', 500);
}

$insert->bind_param('ssssssss', $subject, $course, $section, $schedule_date, $start_time, $end_time, $room, $faculty);

if (!$insert->execute()) {
    error_log('Schedule insert error: ' . $insert->error);
    jsonResponse(false, 'The schedule could not be saved. Check the database structure and permissions.', 500);
}

jsonResponse(true, 'Schedule created successfully.', 200, ['id' => $insert->insert_id]);
