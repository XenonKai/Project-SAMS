<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function respond($success, $message, $status = 200, $extra = []) {
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    respond(false, 'Unauthorized access.', 403);
}

require 'db.php';

$student_id = (int)($_SESSION['user_id'] ?? 0);
$schedule_id = (int)($_POST['schedule_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$schedule_id || !in_array($action, ['time_in', 'time_out'], true)) {
    respond(false, 'Choose a valid schedule and attendance action.', 400);
}

$conn->query("CREATE TABLE IF NOT EXISTS attendance 
        (id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        schedule_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('present','late','absent','excused') NOT NULL DEFAULT 'present',
        time_in DATETIME NULL,
        time_out DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY student_schedule_date (student_id, schedule_id, attendance_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare("
    SELECT s.id, s.schedule_date, s.start_time, s.end_time
    FROM student_subjects ss
    JOIN schedules s ON s.id = ss.schedule_id
    WHERE ss.student_id = ?
      AND s.id = ?
    LIMIT 1
");

if (!$stmt) {
    error_log('Schedule lookup preparation failed: ' . $conn->error);
    respond(false, 'Attendance could not be saved.', 500);
}

$stmt->bind_param('ii', $student_id, $schedule_id);

if (!$stmt->execute()) {
    error_log('Schedule lookup failed: ' . $stmt->error);
    respond(false, 'Attendance could not be saved.', 500);
}

$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    respond(false, 'That schedule is not enrolled for your account.', 404);
}

date_default_timezone_set('Asia/Manila');

$now = new DateTimeImmutable('now');
$today = $now->format('Y-m-d');

if ($schedule['schedule_date'] !== $today) {
    respond(false, 'Time in and time out are available only on the scheduled date.', 400);
}

$start = new DateTimeImmutable($schedule['schedule_date'] . ' ' . $schedule['start_time']);
$end = new DateTimeImmutable($schedule['schedule_date'] . ' ' . $schedule['end_time']);

$presentDeadline = $start->modify('+15 minutes');
$lateDeadline = $start->modify('+30 minutes');
$nowSql = $now->format('Y-m-d H:i:s');

$get = $conn->prepare("
    SELECT status, time_in, time_out
    FROM attendance
    WHERE student_id = ?
      AND schedule_id = ?
      AND attendance_date = ?
    LIMIT 1
");

$get->bind_param('iis', $student_id, $schedule_id, $today);
$get->execute();
$existing = $get->get_result()->fetch_assoc();

if ($action === 'time_in') {
    if ($existing && !empty($existing['time_in'])) {
        respond(false, 'Time in has already been recorded for this subject.', 400);
    }

    if ($now > $end) {
        respond(false, 'Time in is closed because the scheduled subject has ended.', 400);
    }

    if ($now <= $presentDeadline) {
        $status = 'present';
        $statusMessage = 'Time in recorded. Attendance status: Present.';
    } elseif ($now <= $lateDeadline) {
        $status = 'late';
        $statusMessage = 'Time in recorded. Attendance status: Late.';
    } else {
        $status = 'absent';
        $statusMessage = 'Time in recorded. Attendance status: Absent.';
    }

    $q = $conn->prepare("
        INSERT INTO attendance
            (student_id, schedule_id, attendance_date, status, time_in)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$q) {
        error_log('Time-in preparation failed: ' . $conn->error);
        respond(false, 'Attendance could not be saved.', 500);
    }

    $q->bind_param('iisss', $student_id, $schedule_id, $today, $status, $nowSql);

    if (!$q->execute()) {
        error_log('Time-in save failed: ' . $q->error);
        respond(false, 'Attendance could not be saved.', 500);
    }

    respond(true, $statusMessage, 200, [
        'attendance' => [
            'status' => $status,
            'time_in' => $nowSql,
            'time_out' => null
        ]
    ]);
}

if (!$existing || empty($existing['time_in'])) {
    respond(false, 'You must time in before timing out.', 400);
}

if (!empty($existing['time_out'])) {
    respond(false, 'Time out has already been recorded for this subject.', 400);
}

if ($now < $start) {
    respond(false, 'Time out is not available before the subject starts.', 400);
}

$q = $conn->prepare("
    UPDATE attendance
    SET time_out = ?
    WHERE student_id = ?
      AND schedule_id = ?
      AND attendance_date = ?
      AND time_out IS NULL
");

if (!$q) {
    error_log('Time-out preparation failed: ' . $conn->error);
    respond(false, 'Attendance could not be saved.', 500);
}

$q->bind_param('siis', $nowSql, $student_id, $schedule_id, $today);

if (!$q->execute()) {
    error_log('Time-out save failed: ' . $q->error);
    respond(false, 'Attendance could not be saved.', 500);
}

respond(true, 'Time out recorded successfully.', 200, [
    'attendance' => [
        'status' => $existing['status'],
        'time_in' => $existing['time_in'],
        'time_out' => $nowSql
    ]
]);