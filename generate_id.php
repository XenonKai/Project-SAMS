<?php

session_start();

include "db.php";


// Only admin can generate IDs
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit();
}


// Get the information from JavaScript
$name = trim($_POST['name'] ?? '');
$type = $_POST['type'] ?? '';


// Check if name is empty
if ($name == '') {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a name."
    ]);

    exit();
}


// Make sure the type is valid
if ($type != 'student' && $type != 'faculty') {

    echo json_encode([
        "success" => false,
        "message" => "Invalid ID type."
    ]);

    exit();
}


// ==================================================
// STUDENT ID
// ==================================================

if ($type == 'student') {

    // Check if this student already has an ID
    $check = $conn->prepare(
        "SELECT student_id 
         FROM users 
         WHERE full_name = ? 
         AND role = 'student'
         AND student_id IS NOT NULL"
    );

    $check->bind_param("s", $name);

    $check->execute();

    $result = $check->get_result();


    // Student already has an ID
    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        echo json_encode([
            "success" => true,
            "existing" => true,
            "id" => $row['student_id'],
            "message" => "This student already has an ID."
        ]);

        exit();
    }


    // Get the latest student ID
    $last = $conn->query(
        "SELECT student_id
         FROM users
         WHERE student_id LIKE 'STU-SAMS-%'
         ORDER BY id DESC
         LIMIT 1"
    );


    if ($last->num_rows > 0) {

        $row = $last->fetch_assoc();

        $lastID = $row['student_id'];

        // Get number from STU-SAMS-0001
        $number = (int) str_replace(
            "STU-SAMS-",
            "",
            $lastID
        );

        $number++;

    } else {

        $number = 1;

    }


    // Create new student ID
    $newID = "STU-SAMS-" . str_pad(
        $number,
        4,
        "0",
        STR_PAD_LEFT
    );


    // Insert temporary student record
    $insert = $conn->prepare(
        "INSERT INTO users
        (role, full_name, student_id, status)
        VALUES ('student', ?, ?, 'ID Generated')"
    );

    $insert->bind_param(
        "ss",
        $name,
        $newID
    );


    if ($insert->execute()) {

        echo json_encode([
            "success" => true,
            "existing" => false,
            "id" => $newID,
            "message" => "Student ID generated successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Failed to save Student ID."
        ]);
    }

}


// ==================================================
// FACULTY ID
// ==================================================

else if ($type == 'faculty') {

    // Check if this faculty already has an ID
    $check = $conn->prepare(
        "SELECT faculty_id
         FROM users
         WHERE full_name = ?
         AND role = 'faculty'
         AND faculty_id IS NOT NULL"
    );

    $check->bind_param("s", $name);

    $check->execute();

    $result = $check->get_result();


    // Faculty already has an ID
    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        echo json_encode([
            "success" => true,
            "existing" => true,
            "id" => $row['faculty_id'],
            "message" => "This faculty already has an ID."
        ]);

        exit();
    }


    // Get latest faculty ID
    $last = $conn->query(
        "SELECT faculty_id
         FROM users
         WHERE faculty_id LIKE 'FAC-SAMS-%'
         ORDER BY id DESC
         LIMIT 1"
    );


    if ($last->num_rows > 0) {

        $row = $last->fetch_assoc();

        $lastID = $row['faculty_id'];

        $number = (int) str_replace(
            "FAC-SAMS-",
            "",
            $lastID
        );

        $number++;

    } else {

        $number = 1;

    }


    // Create new faculty ID
    $newID = "FAC-SAMS-" . str_pad(
        $number,
        4,
        "0",
        STR_PAD_LEFT
    );


    // Insert temporary faculty record
    $insert = $conn->prepare(
        "INSERT INTO users
        (role, full_name, faculty_id, status)
        VALUES ('faculty', ?, ?, 'ID Generated')"
    );

    $insert->bind_param(
        "ss",
        $name,
        $newID
    );


    if ($insert->execute()) {

        echo json_encode([
            "success" => true,
            "existing" => false,
            "id" => $newID,
            "message" => "Faculty ID generated successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Failed to save Faculty ID."
        ]);
    }

}

?>