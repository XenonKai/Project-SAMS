<?php
session_start(); header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized access.']); exit(); }
require 'db.php'; $student_id=(int)($_POST['student_id']??0); $schedule_id=(int)($_POST['schedule_id']??0);
if(!$student_id||!$schedule_id){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Select both a student and a subject.']);exit();}
$conn->query("CREATE TABLE IF NOT EXISTS student_subjects (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, schedule_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_enrollment (student_id,schedule_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$check=$conn->prepare("SELECT id FROM users WHERE id=? AND role='student' AND status='Registered'");$check->bind_param('i',$student_id);$check->execute();if(!$check->get_result()->fetch_assoc()){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Student account not found or not registered.']);exit();}
$insert=$conn->prepare('INSERT IGNORE INTO student_subjects (student_id,schedule_id) VALUES (?,?)');$insert->bind_param('ii',$student_id,$schedule_id);if(!$insert->execute()){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Assignment could not be saved.']);exit();}echo json_encode(['success'=>true,'message'=>$insert->affected_rows?'Student assigned successfully.':'Student is already assigned to this subject.']);
