<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);

    exit();
}

require 'db.php';


// Create schedules table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS schedules (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql)) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to create schedules table: ' . $conn->error
    ]);

    exit();
}


// Get schedules
$sql = "SELECT 
            id,
            subject,
            course,
            section,
            schedule_date,
            start_time,
            end_time,
            room,
            faculty_id,
            faculty
        FROM schedules
        ORDER BY schedule_date, start_time";

$result = $conn->query($sql);

if (!$result) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to load schedules: ' . $conn->error
    ]);

    exit();
}


$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}


echo json_encode([
    'success' => true,
    'schedules' => $schedules
]);

exit();
?>