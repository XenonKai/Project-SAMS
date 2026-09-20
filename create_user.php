<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function respond(bool $success, string $message, int $status = 200, array $extra = []): void {
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    respond(false, 'Unauthorized access.', 403);
}
require 'db.php';

$role = strtolower(trim($_POST['role'] ?? ''));
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course = trim($_POST['course'] ?? '');
$year_level = trim($_POST['year_level'] ?? '');
$department = trim($_POST['department'] ?? '');

$shs_year_levels = ['Grade 11', 'Grade 12'];
$college_year_levels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
$shs_strands = ['STEM', 'HUMSS', 'ABM', 'GAS'];
$college_courses = ['BSCS', 'BSIT', 'BSHM', 'BSBA'];

if (!in_array($role, ['student', 'faculty'], true)) respond(false, 'Choose Student or Faculty.', 400);
if (strlen($full_name) < 2) respond(false, 'Enter the complete name.', 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Enter a valid email address.', 400);
if ($phone === '') respond(false, 'Enter a phone number.', 400);
if ($role === 'student' && ($course === '' || $year_level === '')) respond(false, 'Course and grade/year level are required for students.', 400);
if ($role === 'student' && !in_array($year_level, array_merge($shs_year_levels, $college_year_levels), true)) respond(false, 'Choose a valid grade/year level.', 400);
if ($role === 'student' && in_array($year_level, $shs_year_levels, true) && !in_array($course, $shs_strands, true)) respond(false, 'Grade 11 and Grade 12 students must use an SHS strand.', 400);
if ($role === 'student' && in_array($year_level, $college_year_levels, true) && !in_array($course, $college_courses, true)) respond(false, 'College students must use a college course.', 400);
if ($role === 'faculty' && $department === '') respond(false, 'Department is required for faculty.', 400);

$check = $conn->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
$check->bind_param('ss', $email, $phone);
$check->execute();
if ($check->get_result()->fetch_assoc()) respond(false, 'That email or phone number is already registered.', 409);
$check->close();

// The plaintext password is returned only in this response; only its hash is stored.
$plain_password = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

if ($role === 'student') {
    $student_id = 'STU-SAMS-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    $sql = $conn->prepare('INSERT INTO users (role, full_name, phone, email, password, status, student_id, course, year_level) VALUES (?, ?, ?, ?, ?, "Registered", ?, ?, ?)');
    $sql->bind_param('ssssssss', $role, $full_name, $phone, $email, $hashed_password, $student_id, $course, $year_level);
    $account_id = $student_id;
} else {
    $faculty_id = 'FAC-SAMS-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    $sql = $conn->prepare('INSERT INTO users (role, full_name, phone, email, password, status, faculty_id, department) VALUES (?, ?, ?, ?, ?, "Registered", ?, ?)');
    $sql->bind_param('sssssss', $role, $full_name, $phone, $email, $hashed_password, $faculty_id, $department);
    $account_id = $faculty_id;
}

if (!$sql->execute()) {
    error_log('Create account error: ' . $sql->error);
    respond(false, 'The account could not be created. Please try again.', 500);
}

respond(true, 'Account created successfully. Give these credentials to the user.', 200, [
    'account_id' => $account_id,
    'name' => $full_name,
    'email' => $email,
    'password' => $plain_password,
    'role' => $role
]);
