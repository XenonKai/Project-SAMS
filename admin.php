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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <button class="primary" id="addScheduleBtn" type="button">Add New Schedule</button>
        <button id="viewSchedulesBtn" type="button">View All Schedules</button>
        <div id="scheduleOutput" class="admin-output hidden" aria-live="polite"></div>
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
        <button id="assignFacultyBtn" type="button">Assign</button>
        <div id="facultyOutput" class="admin-output hidden" aria-live="polite"></div>
      </div>

      <div class="card admin">
        <h3>🆔 Generate IDs</h3>
        <p>Generate Student / Faculty IDs before they can register</p>
        <input type="text" id="idName" placeholder="Enter name for ID">
        <button class="primary" id="generateStudentBtn" type="button">Generate Student ID</button>
        <button id="generateFacultyBtn" type="button">Generate Faculty ID</button>
        <p id="generatedResult" class="admin-output" aria-live="polite">Enter a name, then choose an ID type.</p>
      </div>

      <div class="card admin">
        <h3>👀 Real-Time Activity Monitor</h3>
        <div id="activityLogs">
          <p><b>Live:</b> John (BSCS 2A) Timed In - 8:01 AM</p>
          <p><b>Live:</b> Ma'am Santos confirmed excused - 8:05 AM</p>
          <p><b>Live:</b> Admin generated 5 IDs - 8:10 AM</p>
        </div>
        <button id="viewLogsBtn" type="button">View Full Logs</button>
        <div id="logsOutput" class="admin-output hidden" aria-live="polite"></div>
      </div>
    </div>
  </div>

  <div id="scheduleModal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="scheduleModalTitle">
    <div class="modal-content">
      <button type="button" class="modal-close" id="closeScheduleBtn" aria-label="Close">×</button>
      <h2 id="scheduleModalTitle">Create New Schedule</h2>
      <p class="modal-subtitle">Add a class schedule that will appear in the schedule list.</p>
      <form id="scheduleForm">
        <input name="subject" placeholder="Subject name" required>
        <input name="course" placeholder="Course / program" required>
        <input name="section" placeholder="Section" required>
        <input name="schedule_date" type="date" required>
        <div class="form-row">
          <input name="start_time" type="time" required>
          <input name="end_time" type="time" required>
        </div>
        <input name="room" placeholder="Room (optional)">
        <input name="faculty" placeholder="Faculty (optional)">
        <button class="primary" type="submit">Save Schedule</button>
        <div id="scheduleFormOutput" class="admin-output hidden" aria-live="polite"></div>
      </form>
    </div>
  </div>

  <script src="admin.js"></script>
</body>
</html>
