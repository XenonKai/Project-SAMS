<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css?v=4">
  <title>Admin Dashboard - SAMS</title>
</head>
<body>
  <div class="header"><h2>ACLC College of Malolos - SAMS / Admin</h2><p>Welcome, <?php echo $admin_name; ?> <a href="logout.php">Logout</a></p></div>
  <div class="container">
    <div class="dashboard-grid">
      <div class="card admin account-card">
        <h3>👤 Create User Account</h3>
        <p>The system generates a one-time password. Save or give the credentials to the user.</p>
        <form id="createUserForm">
          <select name="role" id="accountRole" required><option value="">Select account type</option><option value="student">Student</option><option value="faculty">Faculty</option></select>
          <input name="full_name" placeholder="Full name" required>
          <input name="email" type="email" placeholder="Email address" required>
          <input name="phone" placeholder="Phone number" required>
          <div id="studentAccountFields" class="hidden"><select name="year_level"><option value="">Grade / Year level</option><option>Grade 11</option><option>Grade 12</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select><input name="course" placeholder="Strand / Course"></div>
          <div id="facultyAccountFields" class="hidden"><input name="department" placeholder="Department"></div>
          <button class="primary" type="submit">Create Account & Generate Password</button>
          <div id="accountOutput" class="admin-output hidden" aria-live="polite"></div>
        </form>
      </div>
      <div class="card admin"><h3>🗓️ Manage Schedules</h3><p>Create and edit class schedules for all courses</p><button class="primary" id="addScheduleBtn" type="button">Add New Schedule</button><button id="viewSchedulesBtn" type="button">View All Schedules</button><div id="scheduleOutput" class="admin-output hidden"></div></div>
      <div class="card admin"><h3>👀 Real-Time Activity Monitor</h3><div id="activityLogs"><p><b>Live:</b> John (BSCS 2A) Timed In - 8:01 AM</p><p><b>Live:</b> Ma'am Santos confirmed excused - 8:05 AM</p></div><button id="viewLogsBtn" type="button">View Full Logs</button><div id="logsOutput" class="admin-output hidden"></div></div>
    </div>
  </div>
  <div id="scheduleModal" class="modal hidden" role="dialog" aria-modal="true"><div class="modal-content"><button type="button" class="modal-close" id="closeScheduleBtn">×</button><h2>Create New Schedule</h2><p class="modal-subtitle">Add a class schedule that will appear in the schedule list.</p><form id="scheduleForm"><input name="subject" placeholder="Subject name" required><input name="course" placeholder="Course / program" required><input name="section" placeholder="Section" required><input name="schedule_date" type="date" required><div class="form-row"><input name="start_time" type="time" required><input name="end_time" type="time" required></div><input name="room" placeholder="Room (optional)"><input name="faculty" placeholder="Faculty (optional)"><button class="primary" type="submit">Save Schedule</button><div id="scheduleFormOutput" class="admin-output hidden"></div></form></div></div>
  <script src="admin.js?v=4"></script>
</body>
</html>
