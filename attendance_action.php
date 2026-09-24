<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
function respond($success, $message, $status = 200, $extra = []) { http_response_code($status); echo json_encode(array_merge(['success'=>$success,'message'=>$message], $extra)); exit(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') respond(false, 'Unauthorized access.', 403);
require 'db.php';
$student_id = (int)($_SESSION['user_id'] ?? 0); $schedule_id = (int)($_POST['schedule_id'] ?? 0); $action = $_POST['action'] ?? ''; $status = strtolower(trim($_POST['status'] ?? 'present'));
$allowed = ['present','late','absent','excused'];
if (!$schedule_id || !in_array($action, ['time_in','time_out'], true) || !in_array($status, $allowed, true)) respond(false, 'Choose a valid schedule, action, and attendance status.', 400);
$conn->query("CREATE TABLE IF NOT EXISTS attendance (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, schedule_id INT NOT NULL, attendance_date DATE NOT NULL, status ENUM('present','late','absent','excused') NOT NULL DEFAULT 'present', time_in DATETIME NULL, time_out DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY student_schedule_date (student_id,schedule_id,attendance_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$stmt = $conn->prepare("SELECT s.id,s.schedule_date FROM student_subjects ss JOIN schedules s ON s.id=ss.schedule_id WHERE ss.student_id=? AND s.id=? LIMIT 1"); $stmt->bind_param('ii',$student_id,$schedule_id); $stmt->execute(); $schedule=$stmt->get_result()->fetch_assoc();
if (!$schedule) respond(false, 'That schedule is not enrolled for your account.', 404);
$today = date('Y-m-d'); if ($schedule['schedule_date'] !== $today) respond(false, 'Time in and time out are available only on the scheduled date.', 400);
$now = date('Y-m-d H:i:s');
$conn->begin_transaction();
if ($action === 'time_in') {
  $q=$conn->prepare("INSERT INTO attendance (student_id,schedule_id,attendance_date,status,time_in) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),time_in=VALUES(time_in)"); $q->bind_param('iisss',$student_id,$schedule_id,$today,$status,$now); $message='Time in recorded successfully.';
} else {
  $q=$conn->prepare("INSERT INTO attendance (student_id,schedule_id,attendance_date,status,time_out) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),time_out=VALUES(time_out)"); $q->bind_param('iisss',$student_id,$schedule_id,$today,$status,$now); $message='Time out recorded successfully.';
}
if (!$q->execute()) { $conn->rollback(); respond(false, 'Attendance could not be saved.', 500); }
$conn->commit();
$get=$conn->prepare('SELECT status,time_in,time_out FROM attendance WHERE student_id=? AND schedule_id=? AND attendance_date=?'); $get->bind_param('iis',$student_id,$schedule_id,$today); $get->execute(); respond(true,$message,200,['attendance'=>$get->get_result()->fetch_assoc()]);
