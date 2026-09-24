<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized access.']); exit(); }
require 'db.php';
$conn->query("CREATE TABLE IF NOT EXISTS student_subjects (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, schedule_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_enrollment (student_id, schedule_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$sql = "SELECT ss.id, u.full_name AS student_name, u.student_id, u.course, u.year_level, s.subject, s.section, s.schedule_date, s.start_time, s.end_time FROM student_subjects ss JOIN users u ON u.id=ss.student_id JOIN schedules s ON s.id=ss.schedule_id WHERE u.role='student' AND u.status='Registered' ORDER BY s.schedule_date, s.start_time, u.full_name";
$result = $conn->query($sql); $assignments = [];
if ($result) while ($row = $result->fetch_assoc()) $assignments[] = $row;
echo json_encode(['success'=>true,'assignments'=>$assignments]);
