<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

$name = trim($_POST['name'] ?? '');
$type = strtolower(trim((string)($_POST['type'] ?? '')));

if ($name === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a name.'
    ]);
    exit();
}

if (!in_array($type, ['student', 'faculty'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID type.'
    ]);
    exit();
}

function buildNextId(mysqli $conn, string $prefix, string $column, string $pattern): string
{
    $last = $conn->query(
        "SELECT $column FROM users WHERE $column LIKE '$pattern' ORDER BY CAST(REPLACE($column, '$prefix', '') AS UNSIGNED) DESC LIMIT 1"
    );

    $number = 1;
    if ($last && $last->num_rows > 0) {
        $row = $last->fetch_assoc();
        $value = (string)($row[$column] ?? '');
        $numericPart = preg_replace('/[^0-9]/', '', $value);
        if ($numericPart !== '') {
            $number = (int)$numericPart + 1;
        }
    }

    return $prefix . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
}

if ($type === 'student') {
    $check = $conn->prepare(
        "SELECT student_id FROM users WHERE LOWER(full_name) = LOWER(?) AND role = 'student' AND student_id IS NOT NULL LIMIT 1"
    );
    $check->bind_param('s', $name);
    $check->execute();
    $result = $check->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'existing' => true,
            'id' => $row['student_id'],
            'message' => 'This student already has an ID.'
        ]);
        exit();
    }

    $newID = buildNextId($conn, 'STU-SAMS-', 'student_id', 'STU-SAMS-%');

    $insert = $conn->prepare(
        "INSERT INTO users (role, full_name, student_id, status) VALUES ('student', ?, ?, 'ID Generated')"
    );
    $insert->bind_param('ss', $name, $newID);

    if ($insert->execute()) {
        echo json_encode([
            'success' => true,
            'existing' => false,
            'id' => $newID,
            'message' => 'Student ID generated successfully.'
        ]);
        exit();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save Student ID.'
    ]);
    exit();
}

$check = $conn->prepare(
    "SELECT faculty_id FROM users WHERE LOWER(full_name) = LOWER(?) AND role = 'faculty' AND faculty_id IS NOT NULL LIMIT 1"
);
$check->bind_param('s', $name);
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'existing' => true,
        'id' => $row['faculty_id'],
        'message' => 'This faculty already has an ID.'
    ]);
    exit();
}

$newID = buildNextId($conn, 'FAC-SAMS-', 'faculty_id', 'FAC-SAMS-%');

$insert = $conn->prepare(
    "INSERT INTO users (role, full_name, faculty_id, status) VALUES ('faculty', ?, ?, 'ID Generated')"
);
$insert->bind_param('ss', $name, $newID);

if ($insert->execute()) {
    echo json_encode([
        'success' => true,
        'existing' => false,
        'id' => $newID,
        'message' => 'Faculty ID generated successfully.'
    ]);
    exit();
}

http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Failed to save Faculty ID.'
]);
