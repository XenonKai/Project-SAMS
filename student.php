<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
  header("Location: index.php"); exit();
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="student.css"><title>Student Dashboard - SAMS</title></head>
<body>
<div class="header"><h2>ACLC College of Malolos - SAMS / Student</h2><p>Welcome, <?php echo $_SESSION['name']; ?> | <a href="logout.php" style="color:#ffcc00;">Logout</a></p></div>
<div class="container">
<div class="dashboard-grid">
  <div class="card"><h3>📚 Subjects Enrolled</h3><ul><li>Capstone 1</li><li>Web Development</li><li>Data Structures</li><li>Networking 101</li></ul></div>
  <div class="card">
    <h3>🕒 Schedules & Time In/Out</h3>
    <p>Current: Web Dev - 8:00-9:30 AM</p>
    <button class="primary" onclick="alert('Time In recorded: '+new Date().toLocaleTimeString())">Time In</button>
    <button onclick="alert('Time Out recorded')">Time Out</button>
    <select><option>Present</option><option>Late</option><option>Absent (auto if no Time Out & Time In next subject)</option><option>Excused - Needs Faculty Confirmation</option></select>
  </div>
  <div class="card"><h3>📊 Attendance Status</h3><p>Present: 18 / 20</p><p>Late: 1 | Absent: 1 | Excused: 0</p><progress value="90" max="100" style="width:100%"></progress></div>
  <div class="card"><h3>📅 Event Calendar</h3><ul><li>Aug 25 - Buwan ng Wika</li><li>Sept 1 - Prelim Exams</li><li>Sept 15 - Foundation Day</li></ul></div>
</div>
</div>
</body></html>
