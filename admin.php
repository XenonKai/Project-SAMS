<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="admin.css">
  <title>Admin Dashboard - SAMS</title>
</head>

<body>
  <div class="header">
    <h2>ACLC College of Malolos - SAMS / Admin</h2>
    <p>Welcome, <?php echo $admin_name; ?> <a href="logout.php" style="color:#ffcc00;">Logout</a></p>
  </div>

  <div class="container">
    <div class="dashboard-grid">
      <div class="card admin">
        <h3>🗓️ Manage Schedules</h3>
        <p>Create and edit class schedules for all courses</p>
        <button class="primary" id="addScheduleBtn">Add New Schedule</button>
        <button id="viewSchedulesBtn">View All Schedules</button>
      </div>

      <div class="card admin">
        <h3>👨‍🏫 Assign Faculties</h3>
        <p>Assign teachers to subjects and sections</p>
        <select id="facultySelect">
          <option value="">Select Faculty</option>
          <option value="Juan Dela Cruz">Juan Dela Cruz - Science and Tech</option>
          <option value="Maria Santos">Maria Santos - ICT</option>
          <option value="Pedro Reyes">Pedro Reyes - Mathematics</option>
        </select>
        <button id="assignFacultyBtn">Assign</button>
      </div>

      <div class="card admin">
        <h3>🆔 Generate IDs</h3>
        <p>Generate Student / Faculty IDs before they can register</p>
        <input type="text" id="idName" placeholder="Enter name for ID">
        <button class="primary" id="generateStudentBtn">Generate Student ID</button>
        <button id="generateFacultyBtn">Generate Faculty ID</button>
        <p id="generatedResult" style="font-size:12px; margin-top:10px;">Example Generated: ACLC-2026-00123</p>
      </div>

      <div class="card admin">
        <h3>👀 Real-Time Activity Monitor</h3>
        <div id="activityLogs">
          <p><b>Live:</b> John (BSCS 2A) Timed In - 8:01 AM</p>
          <p><b>Live:</b> Ma'am Santos confirmed excused - 8:05 AM</p>
          <p><b>Live:</b> Admin generated 5 IDs - 8:10 AM</p>
        </div>
        <button id="viewLogsBtn">View Full Logs</button>
      </div>
    </div>
  </div>

  <script src="admin.js"></script>
</body>
</html>
