<?php
/**
 * One-time presentation/demo user seeder.
 * Run from the project directory with:
 *   php seed_demo_users.php
 *
 * This script is intentionally CLI-only so the demo credentials cannot be
 * created by a visitor through the browser.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this file from the command line.\n");
}

require __DIR__ . '/db.php';

$demoPassword = '1029384756';
$demoUsers = [
    [
        'role' => 'admin',
        'full_name' => 'Aldion Beldad',
        'phone' => '09161196693',
        'email' => null,
        'student_id' => null,
        'course' => null,
        'year_level' => null,
        'faculty_id' => null,
        'department' => null,
    ],
    [
        'role' => 'student',
        'full_name' => 'Ivan Pasco',
        'phone' => '09123789456',
        'email' => 'ivanchristmas@gmail.com',
        'student_id' => 'DEMO-STU-IVAN',
        'course' => 'Bachelor of Science in Computer Science',
        'year_level' => '3rd Year',
        'faculty_id' => null,
        'department' => null,
    ],
    [
        'role' => 'faculty',
        'full_name' => 'Adrian Laos',
        'phone' => '09456789123',
        'email' => 'laosna@gmail.com',
        'student_id' => null,
        'course' => null,
        'year_level' => null,
        'faculty_id' => 'DEMO-FAC-ADRIAN',
        'department' => 'Technology and Science',
    ],
];

$conn->begin_transaction();

try {
    $passwordHash = password_hash($demoPassword, PASSWORD_DEFAULT);
    $lookup = $conn->prepare('SELECT id FROM users WHERE phone = ? OR (email IS NOT NULL AND email = ?) LIMIT 1');
    $update = $conn->prepare(
        'UPDATE users SET role = ?, full_name = ?, phone = ?, email = ?, password = ?, status = "Registered", student_id = ?, course = ?, year_level = ?, faculty_id = ?, department = ? WHERE id = ?'
    );
    $insert = $conn->prepare(
        'INSERT INTO users (role, full_name, phone, email, password, status, student_id, course, year_level, faculty_id, department) VALUES (?, ?, ?, ?, ?, "Registered", ?, ?, ?, ?, ?)'
    );

    foreach ($demoUsers as $user) {
        $email = $user['email'];
        $lookup->bind_param('ss', $user['phone'], $email);
        $lookup->execute();
        $existing = $lookup->get_result()->fetch_assoc();

        if ($existing) {
            $id = (int) $existing['id'];
            $update->bind_param(
                'ssssssssssi',
                $user['role'],
                $user['full_name'],
                $user['phone'],
                $user['email'],
                $passwordHash,
                $user['student_id'],
                $user['course'],
                $user['year_level'],
                $user['faculty_id'],
                $user['department'],
                $id
            );
            $update->execute();
        } else {
            $insert->bind_param(
                'sssssssss',
                $user['role'],
                $user['full_name'],
                $user['phone'],
                $user['email'],
                $passwordHash,
                $user['student_id'],
                $user['course'],
                $user['year_level'],
                $user['faculty_id'],
                $user['department']
            );
            $insert->execute();
        }
    }

    $conn->commit();
    echo "Demo users inserted/updated successfully.\n";
    echo "Shared presentation password: {$demoPassword}\n";
} catch (Throwable $error) {
    $conn->rollback();
    fwrite(STDERR, "Unable to seed demo users: {$error->getMessage()}\n");
    exit(1);
}
