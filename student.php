<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// These tables are also created by the admin endpoints, but keeping the read page
// self-contained makes an existing installation work without a manual migration.
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
$conn->query("CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    schedule_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','late','absent','excused') NOT NULL DEFAULT 'present',
    time_in DATETIME NULL,
    time_out DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY student_schedule_date (student_id, schedule_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$student_id = (int)($_SESSION['user_id'] ?? 0);
$student_name = e($_SESSION['name'] ?? 'Student');
$subjects = [];
$stmt = $conn->prepare("SELECT s.id, s.subject, s.course, s.section, s.schedule_date, s.start_time, s.end_time, s.room, s.faculty,
        a.status, a.time_in, a.time_out
    FROM student_subjects ss
    JOIN schedules s ON s.id = ss.schedule_id
    LEFT JOIN attendance a ON a.schedule_id = s.id AND a.student_id = ss.student_id AND a.attendance_date = s.schedule_date
    WHERE ss.student_id = ?
    ORDER BY s.schedule_date ASC, s.start_time ASC, s.subject ASC");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $subjects[] = $row;
$stmt->close();

$counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0];
foreach ($subjects as $subject) {
    if (!empty($subject['status']) && isset($counts[$subject['status']])) $counts[$subject['status']]++;
}
$total = array_sum($counts);
$recorded = $total - ($counts['absent'] ?? 0);
$attendance_rate = $total ? (int)round(($recorded / $total) * 100) : 0;
$today = date('Y-m-d');
$status_labels = ['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'excused' => 'Excused - Needs Faculty Confirmation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="student.css?v=7">
  <link rel="stylesheet" href="student-enhancements.css?v=1">
  <title>Student Dashboard - SAMS</title>
</head>
<body>
  <div class="header">
    <div class="header-title"><h2>ACLC College of Malolos - SAMS</h2><span>Student Dashboard</span></div>
    <div class="header-user"><span>Welcome, <strong><?php echo $student_name; ?></strong></span><a href="logout.php">Logout</a></div>
  </div>

  <main class="container">
    <h1>Student Dashboard</h1>
    <p class="subtitle">Your enrolled subjects, schedules, and attendance records.</p>
    <div id="studentMessage" class="student-message hidden"></div>
    <div class="dashboard-grid">
      <div class="card clickable-card" role="link" tabindex="0" onclick="window.open('student_subjects.php', '_blank')" onkeydown="if(event.key==='Enter'||event.key===' ') window.open('student_subjects.php', '_blank')">
        <h3>📚 Subjects Enrolled</h3>
        <p><strong><?php echo count($subjects); ?></strong> subject(s) currently assigned to you.</p>
        <span class="card-link">Open full enrolled-subject table ↗</span>
      </div>

      <div class="card schedule-card">
        <h3>🕒 Schedules &amp; Time In/Out</h3>
        <?php if (!$subjects): ?>
          <p class="empty-state">No subjects have been assigned to you yet.</p>
        <?php else: ?>
          <label for="scheduleChoice">Choose a scheduled subject</label>
          <select id="scheduleChoice">
            <?php foreach ($subjects as $index => $subject): ?>
              <option value="<?php echo (int)$subject['id']; ?>" data-date="<?php echo e($subject['schedule_date']); ?>" <?php echo $index === 0 ? 'selected' : ''; ?>><?php echo e($subject['subject'].' • '.$subject['schedule_date'].' • '.substr($subject['start_time'], 0, 5).'-'.substr($subject['end_time'], 0, 5)); ?></option>
            <?php endforeach; ?>
          </select>
          <div id="selectedScheduleDetails" class="schedule-details"></div>
          <label for="attendanceChoice">Attendance status</label>
          <select id="attendanceChoice">
            <?php foreach ($status_labels as $value => $label): ?><option value="<?php echo $value; ?>"><?php echo e($label); ?></option><?php endforeach; ?>
          </select>
          <div class="action-row"><button class="primary" id="timeInButton" type="button">Time In</button><button id="timeOutButton" type="button">Time Out</button></div>
          <div id="scheduleFeedback" class="feedback" aria-live="polite"></div>
        <?php endif; ?>
      </div>

      <div class="card attendance-card">
        <h3>📊 Attendance Status</h3>
        <div class="attendance-summary"><div><strong><?php echo $counts['present']; ?></strong><span>Present</span></div><div><strong><?php echo $counts['late']; ?></strong><span>Late</span></div><div><strong><?php echo $counts['absent']; ?></strong><span>Absent</span></div><div><strong><?php echo $counts['excused']; ?></strong><span>Excused</span></div></div>
        <p id="attendanceTimeIn">Select a schedule above to view its time in.</p>
        <p><?php echo $recorded; ?> recorded of <?php echo $total; ?> scheduled attendance record(s).</p>
        <progress value="<?php echo $attendance_rate; ?>" max="100"></progress><small><?php echo $attendance_rate; ?>% attendance rate</small>
      </div>
    </div>
  </main>
  <script>
    const subjects = <?php echo json_encode($subjects, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const labels = <?php echo json_encode($status_labels); ?>;
    const today = <?php echo json_encode($today); ?>;
    const choice = document.getElementById('scheduleChoice');
    const statusChoice = document.getElementById('attendanceChoice');
    const details = document.getElementById('selectedScheduleDetails');
    const feedback = document.getElementById('scheduleFeedback');
    const timeInText = document.getElementById('attendanceTimeIn');
    function selectedSubject() { return subjects.find(item => String(item.id) === choice.value); }
    function refreshSchedule() {
      const item = selectedSubject(); if (!item) return;
      details.innerHTML = `<strong>${item.subject}</strong><span>${item.course} ${item.section} · ${item.schedule_date}</span><span>${item.start_time.slice(0,5)}–${item.end_time.slice(0,5)}${item.room ? ' · Room '+item.room : ''}</span>`;
      statusChoice.value = item.status || 'present';
      timeInText.textContent = item.time_in ? `Time in: ${new Date(item.time_in.replace(' ', 'T')).toLocaleString()}${item.time_out ? ` · Time out: ${new Date(item.time_out.replace(' ', 'T')).toLocaleString()}` : ''} · Status: ${labels[item.status] || labels.present}` : `No time in recorded for ${item.subject} yet. Status choice: ${labels[statusChoice.value]}.`;
      document.getElementById('timeInButton').disabled = item.schedule_date !== today;
      document.getElementById('timeOutButton').disabled = !item.time_in || item.schedule_date !== today;
    }
    async function record(action) {
      const item = selectedSubject(); if (!item) return;
      feedback.textContent = 'Saving attendance...';
      const body = new URLSearchParams({ action, schedule_id: item.id, status: statusChoice.value });
      try { const response = await fetch('attendance_action.php', { method: 'POST', body, headers: {'Accept':'application/json'} }); const data = await response.json(); if (!response.ok || !data.success) throw new Error(data.message || 'Unable to save attendance.'); item.status = data.attendance.status; item.time_in = data.attendance.time_in; item.time_out = data.attendance.time_out; feedback.textContent = data.message; refreshSchedule(); } catch (error) { feedback.textContent = error.message; }
    }
    choice?.addEventListener('change', refreshSchedule); statusChoice?.addEventListener('change', refreshSchedule); document.getElementById('timeInButton')?.addEventListener('click', () => record('time_in')); document.getElementById('timeOutButton')?.addEventListener('click', () => record('time_out')); refreshSchedule();
  </script>
</body>
</html>
