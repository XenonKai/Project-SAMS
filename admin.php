<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: index.php"); exit();
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="admin.css"><title>Admin Dashboard - SAMS</title></head>
<body>
<div class="header"><h2>ACLC College of Malolos - SAMS / Admin</h2><p>Welcome, <?php echo $_SESSION['name']; ?> | <a href="logout.php" style="color:#ffcc00;">Logout</a></p></div>
<div class="container">
<div class="dashboard-grid">
  <div class="card admin"><h3>🗓️ Manage Schedules</h3><p>Create and edit class schedules for all courses</p><button class="primary">Add New Schedule</button><button>View All Schedules</button></div>
  <div class="card admin"><h3>👨‍🏫 Assign Faculties</h3><p>Assign teachers to subjects and sections</p><select><option>Select Faculty</option><option>Juan Dela Cruz - Science and Tech</option></select><button>Assign</button></div>
  <div class="card admin"><h3>🆔 Generate IDs</h3><p>Generate Student / Faculty IDs before they can register</p><input placeholder="Enter name for ID"><button class="primary">Generate Student ID</button><button>Generate Faculty ID</button><p style="font-size:12px; margin-top:10px;">Example Generated: ACLC-2026-00123</p></div>
  <div class="card admin"><h3>👀 Real-Time Activity Monitor</h3><p><b>Live:</b> John (BSCS 2A) Timed In - 8:01 AM</p><p><b>Live:</b> Ma'am Santos confirmed excused - 8:05 AM</p><p><b>Live:</b> Admin generated 5 IDs - 8:10 AM</p><button>View Full Logs</button></div>
</div>
</div>
</body></html>
