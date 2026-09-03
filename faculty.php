<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty'){
  header("Location: index.php"); exit();
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="style.css"><title>Faculty Dashboard - SAMS</title></head>
<body>
<div class="header"><h2>ACLC College of Malolos - SAMS / Faculty</h2><p>Welcome, <?php echo $_SESSION['name']; ?> | <a href="logout.php" style="color:#ffcc00;">Logout</a></p></div>
<div class="container">
<div class="dashboard-grid">
  <div class="card"><h3>📖 Subjects to Teach</h3><ul><li>BSCS 2A - Web Dev</li><li>BSIT 1B - Programming 1</li></ul></div>
  <div class="card"><h3>🏫 Year Level & Sections to Attend</h3><p>Grade 11 - STEM A, HUMSS B</p><p>2nd Year - BSCS 2A, 2B</p></div>
  <div class="card"><h3>📋 Masterlist</h3><p>Click to view all sections you handle</p><button class="primary" onclick="alert('Showing Masterlist: BSCS 2A (35 students), BSIT 1B (40 students)')">View Masterlist</button></div>
  <div class="card"><h3>⭐ Advisory Class</h3><p>Advisory: BSCS 2A</p><button>View Advisory Attendance</button></div>
  <div class="card"><h3>✅ Students Attendance Status (Per Subject)</h3><p>Web Dev Today: 28 Present, 2 Late, 5 Absent</p><button class="primary">Confirm Excused Requests</button></div>
</div>
</div>
</body></html>
