<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$faculty_members = [];
$faculty_result = $conn->query("SELECT id, full_name, faculty_id, department FROM users WHERE role = 'faculty' AND status = 'Registered' ORDER BY full_name ASC");
if ($faculty_result) {
    while ($faculty = $faculty_result->fetch_assoc()) {
        $faculty_members[] = $faculty;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css?v=5">
  <title>Admin Dashboard - SAMS</title>
</head>
<body>
  <div class="header">
    <h2>ACLC College of Malolos - SAMS / Admin</h2>
    <p>Welcome, <?php echo $admin_name; ?> <a href="logout.php">Logout</a></p>
  </div>

  <div class="container">
    <div class="dashboard-grid">
      <div class="card admin account-card">
        <h3>👤 Create User Account</h3>
        <p>The system generates a one-time password. Save or give the credentials to the user.</p>
        <form id="createUserForm">
          <select name="role" id="accountRole" required>
            <option value="">Select account type</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
          </select>
          <input name="full_name" placeholder="Full name" required>
          <input name="email" type="email" placeholder="Email address" required>
          <input name="phone" placeholder="Phone number" required>
          <div id="studentAccountFields" class="hidden">
            <select name="year_level" id="yearLevelSelect">
              <option value="">Grade / Year level</option>
              <option>Grade 11</option><option>Grade 12</option><option>1st Year</option>
              <option>2nd Year</option><option>3rd Year</option><option>4th Year</option>
            </select>
            <select name="course" id="courseSelect" aria-label="Strand or course">
              <option value="">Select strand / course</option>
              <optgroup label="Senior High School Strands">
                <option value="STEM" data-level="shs">STEM</option>
                <option value="HUMSS" data-level="shs">HUMSS</option>
                <option value="ABM" data-level="shs">ABM</option>
                <option value="GAS" data-level="shs">GAS</option>
              </optgroup>
              <optgroup label="College Programs">
                <option value="BSCS" data-level="college">BSCS</option>
                <option value="BSIT" data-level="college">BSIT</option>
                <option value="BSHM" data-level="college">BSHM</option>
                <option value="BSBA" data-level="college">BSBA</option>
              </optgroup>
            </select>
          </div>
          <div id="facultyAccountFields" class="hidden"><input name="department" placeholder="Department"></div>
          <button class="primary" type="submit">Create Account &amp; Generate Password</button>
          <div id="accountOutput" class="admin-output hidden" aria-live="polite"></div>
        </form>
      </div>

      <div class="card admin">
        <h3>🗓️ Manage Schedules</h3>
        <p>Create a class schedule and assign a registered faculty member.</p>
        <button class="primary" id="addScheduleBtn" type="button">Add New Schedule</button>
        <button id="viewSchedulesBtn" type="button">View All Schedules</button>
        <div id="scheduleOutput" class="admin-output hidden" aria-live="polite"></div>
      </div>

      <div class="card admin">
        <h3>👀 Real-Time Activity Monitor</h3>
        <div id="activityLogs">
          <p><b>Live:</b> John (BSCS 2A) Timed In - 8:01 AM</p>
          <p><b>Live:</b> Ma'am Santos confirmed excused - 8:05 AM</p>
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
      <p class="modal-subtitle">Assign a faculty member to this subject before saving.</p>
      <form id="scheduleForm">
        <input name="subject" placeholder="Subject name" required>
        <input name="course" placeholder="Course / program" required>
        <input name="section" placeholder="Section" required>
        <select name="faculty" id="scheduleFaculty" required>
          <option value="">Select faculty staff</option>
          <?php foreach ($faculty_members as $faculty): ?>
            <option value="<?php echo htmlspecialchars($faculty['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php echo htmlspecialchars($faculty['full_name'] . ' - ' . ($faculty['department'] ?: 'Faculty'), ENT_QUOTES, 'UTF-8'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!$faculty_members): ?>
          <small class="form-warning">No registered faculty accounts are available. Create a faculty account first.</small>
        <?php endif; ?>
        <input name="schedule_date" type="date" required>
        <div class="form-row"><input name="start_time" type="time" required><input name="end_time" type="time" required></div>
        <input name="room" placeholder="Room (optional)">
        <button class="primary" type="submit" <?php echo !$faculty_members ? 'disabled' : ''; ?>>Save Schedule</button>
        <div id="scheduleFormOutput" class="admin-output hidden" aria-live="polite"></div>
      </form>
    </div>
  </div>
  <script src="admin.js?v=5"></script>
</body>
</html>
