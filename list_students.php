<?php
session_start(); header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized access.']); exit(); }
require 'db.php';$r=$conn->query("SELECT id,full_name,student_id,course,year_level FROM users WHERE role='student' AND status='Registered' ORDER BY full_name");$students=[];while($row=$r->fetch_assoc())$students[]=$row;echo json_encode(['success'=>true,'students'=>$students]);
