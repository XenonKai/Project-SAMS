<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header('Location: index.php');
    exit();
}

$faculty_name = htmlspecialchars($_SESSION['name'] ?? 'Faculty', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - SAMS</title>
    <link rel="stylesheet" href="faculty.css">
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h2>ACLC College of Malolos - SAMS</h2>
            <span>Faculty Dashboard</span>
        </div>

        <div class="header-user">
            <span>Welcome, <strong><?php echo $faculty_name; ?></strong></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Faculty Dashboard</h1>
        <p class="subtitle">Manage your subjects, sections, masterlists, and student attendance.</p>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📖 Subjects to Teach</h3>
                <ul>
                    <li>BSCS 2A - Web Development</li>
                    <li>BSIT 1B - Programming 1</li>
                </ul>
            </div>

            <div class="card">
                <h3>🏫 Year Level & Sections</h3>
                <p><strong>Grade 11</strong></p>
                <p>STEM A<br>HUMSS B</p>
                <p><strong>2nd Year</strong></p>
                <p>BSCS 2A<br>BSCS 2B</p>
            </div>

            <div class="card">
                <h3>📋 Masterlist</h3>
                <p>View the students in the sections assigned to you.</p>
                <button class="primary" onclick="showMasterlist()">View Masterlist</button>
                <div id="masterlist" class="masterlist hidden">
                    <strong>BSCS 2A</strong>
                    <p>35 students</p>
                    <hr>
                    <strong>BSIT 1B</strong>
                    <p>40 students</p>
                </div>
            </div>

            <div class="card">
                <h3>⭐ Advisory Class</h3>
                <p><strong>Advisory:</strong> BSCS 2A</p>
                <button onclick="showAdvisory()">View Advisory Attendance</button>
            </div>

            <div class="card attendance-card">
                <h3>✅ Student Attendance Status</h3>
                <p><strong>Web Development - Today</strong></p>
                <div class="attendance-summary">
                    <div><strong>28</strong><span>Present</span></div>
                    <div><strong>2</strong><span>Late</span></div>
                    <div><strong>5</strong><span>Absent</span></div>
                </div>
                <button class="primary">Confirm Excused Requests</button>
            </div>
        </div>
    </div>

    <script src="faculty.js"></script>
</body>
</html>
