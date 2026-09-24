<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    section VARCHAR(50) NOT NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50) NULL,
    faculty_id INT NULL,
    faculty VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS student_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    schedule_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, schedule_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("ALTER TABLE schedules ADD COLUMN faculty_id INT NULL");

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$faculty_members = [];
$faculty_result = $conn->query("SELECT id, full_name, faculty_id, faculty_profession FROM users WHERE role = 'faculty' AND status = 'Registered' ORDER BY full_name ASC");
if ($faculty_result) while ($faculty = $faculty_result->fetch_assoc()) $faculty_members[] = $faculty;
$student_members = [];
$student_result = $conn->query("SELECT id, full_name, student_id, course, year_level FROM users WHERE role = 'student' AND status = 'Registered' ORDER BY full_name ASC");
if ($student_result) while ($student = $student_result->fetch_assoc()) $student_members[] = $student;
$schedules = [];
$schedule_result = $conn->query("SELECT id, subject, course, section, schedule_date, start_time, end_time, room, faculty_id, faculty FROM schedules ORDER BY schedule_date, start_time");
if ($schedule_result) while ($schedule = $schedule_result->fetch_assoc()) $schedules[] = $schedule;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="admin.css?v=6"><title>Admin Dashboard - SAMS</title></head>
<body>
<div class="header"><h2>ACLC College of Malolos - SAMS / Admin</h2><p>Welcome, <?php echo $admin_name; ?> <a href="logout.php">Logout</a></p></div>
<div class="container"><div class="dashboard-grid">
<div class="card admin account-card"><h3>👤 Create User Account</h3><p>Generate registered credentials for a student or faculty member.</p>
<form id="createUserForm"><select name="role" id="accountRole" required><option value="">Select account type</option><option value="student">Student</option><option value="faculty">Faculty</option></select><input name="full_name" placeholder="Full name" required><input name="email" type="email" placeholder="Email address" required><input name="phone" placeholder="Phone number" required>
<div id="studentAccountFields" class="hidden"><select name="year_level" id="yearLevelSelect"><option value="">Grade / Year level</option><option>Grade 11</option><option>Grade 12</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select><select name="course" id="courseSelect"><option value="">Select strand / course</option><option value="STEM" data-level="shs">STEM</option><option value="HUMSS" data-level="shs">HUMSS</option><option value="ABM" data-level="shs">ABM</option><option value="GAS" data-level="shs">GAS</option><option value="ICT-Programming" data-level="shs">ICT-Programming</option><option value="ICT-CSS" data-level="shs">ICT-CSS</option><option value="ICT-Animation" data-level="shs">ICT-Animation</option><option value="BSCS" data-level="college">BSCS</option><option value="BSEN" data-level="college">BSEN</option><option value="BSAIS" data-level="college">BSAIS</option><option value="ACT" data-level="college">ACT</option></select></div>
<div id="facultyAccountFields" class="hidden"><select name="faculty_expertise"><option value="">Select field of expertise</option><option>Science, Technology, Engineering, and Mathematics (STEM)</option><option>Humanities & Arts</option><option>Social & Behavioral Sciences</option><option>Law, Public Safety, & Governance</option><option>Business & Management</option><option>Health & Medical Sciences</option><option>Information Technology (IT) Services</option><option>Campus Safety & Security</option><option>Student Affairs & Auxiliary Services</option><option>Finance & Corporate Administration</option><option>Facilities & Estates Management</option></select><select name="faculty_profession"><option value="">Select profession</option><option>Master Teacher</option><option>Academic Head</option><option>Dean</option><option>Faculty Staff</option></select></div>
<button class="primary" type="submit">Create Account &amp; Generate Password</button><div id="accountOutput" class="admin-output hidden"></div></form></div>
<div class="card admin"><h3>🗓️ Manage Schedules</h3><p>Create schedules and assign them to registered faculty staff.</p><button class="primary" id="addScheduleBtn" type="button">Add New Schedule</button><button id="viewSchedulesBtn" type="button">View All Schedules</button><div id="scheduleOutput" class="admin-output hidden"></div></div>
<div class="card admin"><h3>🔗 Assign Students</h3><p>Enroll a registered student in a subject and section. The assigned faculty will see the masterlist.</p><form id="assignmentForm"><select name="student_id" required><option value="">Select student</option><?php foreach ($student_members as $student): ?><option value="<?php echo (int)$student['id']; ?>"><?php echo htmlspecialchars($student['full_name'].' - '.$student['course'].' '.$student['year_level'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><select name="schedule_id" required><option value="">Select subject / section</option><?php foreach ($schedules as $schedule): ?><option value="<?php echo (int)$schedule['id']; ?>"><?php echo htmlspecialchars($schedule['subject'].' - '.$schedule['course'].' '.$schedule['section'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><button class="primary" type="submit">Assign Student</button><div id="assignmentOutput" class="admin-output hidden"></div></form></div>
<div class="card admin"><h3>📋 Registered Students</h3><p><?php echo count($student_members); ?> registered student account(s) available for assignment.</p><div class="admin-output">Create an account first, then assign it to every required subject and section above.</div></div>
</div></div>
<div id="scheduleModal" class="modal hidden"><div class="modal-content"><button type="button" class="modal-close" id="closeScheduleBtn">×</button><h2>Create New Schedule</h2><p class="modal-subtitle">Assign a faculty member before saving.</p><form id="scheduleForm"><input name="subject" placeholder="Subject name" required><input name="course" placeholder="Course / program" required><input name="section" placeholder="Section" required><select name="faculty_id" id="scheduleFaculty" required><option value="">Select faculty staff</option><?php foreach ($faculty_members as $faculty): ?><option value="<?php echo (int)$faculty['id']; ?>"><?php echo htmlspecialchars($faculty['full_name'].' - '.($faculty['faculty_profession'] ?: 'Faculty'), ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><input name="schedule_date" type="date" required><div class="form-row"><input name="start_time" type="time" required><input name="end_time" type="time" required></div><input name="room" placeholder="Room (optional)"><button class="primary" type="submit" <?php echo !$faculty_members ? 'disabled' : ''; ?>>Save Schedule</button><div id="scheduleFormOutput" class="admin-output hidden"></div></form></div></div>
<script src="admin.js?v=11"></script></body></html>
