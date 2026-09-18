<?php
session_start();
include 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$login_message = "";
$signup_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        $role = trim($_POST['role'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $student_id = trim($_POST['student_id'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '');
        $faculty_id = trim($_POST['faculty_id'] ?? '');
        $department = trim($_POST['department'] ?? '');
    }
        if ($role !== 'student' && $role !== 'faculty') {
            $signup_message = "Please choose a valid role.";
        } elseif (strlen($full_name) < 2) {
            $signup_message = "Please enter your full name.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_message = "Please enter a valid email address.";
        } elseif (strlen($password) < 8) {
            $signup_message = "Password must be at least 8 characters.";
        } elseif ($phone === '') {
            $signup_message = "Please enter your phone number.";
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
            $check->bind_param("ss", $email, $phone);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            $check->close();

            if ($existing) {
                $signup_message = "Email or phone number is already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                if ($role === 'student') {
                    if ($student_id === '') {
                        $signup_message = "You must enter your Student ID.";
                    } else {
                        $sql = $conn->prepare("INSERT INTO users (role, full_name, phone, email, password, status, student_id, course, year_level) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?)");

$sql->bind_param(
    "ssssssss",
    $role,
    $full_name,
    $phone,
    $email,
    $hashed,
    $student_id,
    $course,
    $year_level
);
                    }
                } else {
                    if ($faculty_id === '') {
                        $signup_message = "You must enter your Faculty ID.";
                    } else {
                        $sql = $conn->prepare("INSERT INTO users (role, full_name, phone, email, password, status, faculty_id, department) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?)");

$sql->bind_param(
    "sssssss",
    $role,
    $full_name,
    $phone,
    $email,
    $hashed,
    $faculty_id,
    $department
);
                    }
                }

                if (isset($sql)) {
                    if ($sql->execute()) {
                        $signup_message = "Account created successfully. You can now log in.";
                    } else {
                        $signup_message = "Error creating account: " . $conn->error;
                    }
                    $sql->close();
                }
            }
        }
    }

    if (isset($_POST['login'])) {
        $input = trim($_POST['login_input'] ?? '');
        $pass = $_POST['login_pass'] ?? '';

        $sql = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");

$sql->bind_param("ss", $input, $input);
        $sql->execute();
        $user = $sql->get_result()->fetch_assoc();
        $sql->close();

        if (!$user) {
    $login_message = "Invalid email/phone or password.";
} elseif ($user['status'] !== 'Registered') {
    $login_message = "Your account is not registered yet.";
} elseif (!password_verify($pass, $user['password'])) {
    $login_message = "Invalid email/phone or password.";
} else {
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SAMS - ACLC College of Malolos</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="header">
        <h1>ACLC College of Malolos</h1>
        <h3><span>SAMS</span> / Student Attendance Management System</h3>
    </div>

    <div class="container">
        <?php if ($login_message !== ""): ?>
            <div class="error"><?php echo htmlspecialchars($login_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($signup_message !== ""): ?>
            <div class="<?php echo strpos($signup_message, 'success') !== false || strpos($signup_message, 'created successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($signup_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div style="text-align:center; margin-bottom:10px;">
            <button type="button" onclick="showLogin()">Log In</button>
            <button type="button" onclick="showSignup()">Sign Up</button>
        </div>

 <div id="signupPanel" class="hidden">

    <h3>Select Role to Create Account</h3>

    <div class="role-cards">
        <button
            type="button"
            class="role-card"
            id="card-student"
            onclick="selectRole('student')"
        >
            👨‍🎓<br>
            <b>Student</b>
        </button>

        <button
            type="button"
            class="role-card"
            id="card-faculty"
            onclick="selectRole('faculty')"
        >
            👨‍🏫<br>
            <b>Faculty</b>
        </button>
    </div>

    <form method="POST" id="signupForm">

        <input
            type="hidden"
            name="role"
            id="roleInput"
            required
        >

        <h3>
            Create Account -
            <span id="roleLabel">Choose a role</span>
        </h3>

        <input
            type="text"
            name="full_name"
            placeholder="Full Name"
            required
        >

        <input
            type="text"
            name="phone"
            placeholder="Phone Number"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email Address"
            required
        >

        <input
            type="password"
            name="password"
            id="pass"
            placeholder="Create Password"
            minlength="8"
            required
        >

        <button type="button" onclick="togglePass()">
            Show / Hide Password
        </button>

        <div id="studentFields" class="hidden">

            <input
                type="text"
                name="student_id"
                placeholder="Student ID from Admin"
            >

            <select name="course">
                <option value="">Select Course/Program</option>
                <option>STEM</option>
                <option>HUMSS</option>
                <option>ABM</option>
                <option>ICT-Programming</option>
                <option>ICT-Animation</option>
                <option>ICT-CSS</option>
                <option>BSEN</option>
                <option>ACT</option>
                <option>BSAIS</option>
                <option>BSCS</option>
            </select>

            <select name="year_level">
                <option value="">Select Grade/Year Level</option>
                <option>Grade 11</option>
                <option>Grade 12</option>
                <option>1st Year</option>
                <option>2nd Year</option>
                <option>3rd Year</option>
                <option>4th Year</option>
            </select>

        </div>

        <div id="facultyFields" class="hidden">

            <input
                type="text"
                name="faculty_id"
                placeholder="Faculty ID from Admin"
            >

            <select name="department">
                <option value="">Select Department</option>
                <option>Science and Tech</option>
                <option>Arts and Humanities</option>
                <option>Social and Business Sciences</option>
                <option>Professional Fields</option>
            </select>

        </div>

        <button
            type="submit"
            name="signup"
            class="primary"
        >
            Create Account
        </button>

    </form>

</div>

                <div id="studentFields" class="hidden">
                    <input type="text" name="student_id" placeholder="Student ID (from Admin)">
                    <select name="course">
                        <option value="">Select Course/Program</option>
                        <option>STEM</option>
                        <option>HUMSS</option>
                        <option>ABM</option>
                        <option>ICT-Programming</option>
                        <option>ICT-Animation</option>
                        <option>ICT-CSS</option>
                        <option>BSEN</option>
                        <option>ACT</option>
                        <option>BSAIS</option>
                        <option>BSCS</option>
                    </select>

                    <select name="year_level">
                        <option value="">Select Grade/Year Level</option>
                        <option>Grade 11</option>
                        <option>Grade 12</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option>3rd Year</option>
                        <option>4th Year</option>
                    </select>
                </div>

                <div id="facultyFields" class="hidden">
                    <input type="text" name="faculty_id" placeholder="Faculty ID (from Admin)">
                    <select name="department">
                        <option value="">Select Department</option>
                        <option>Science and Tech</option>
                        <option>Arts and Humanities</option>
                        <option>Social and Business Sciences</option>
                        <option>Professional Fields</option>
                    </select>
                </div>

                <button type="submit" name="signup" class="primary">Create Account</button>
            </form>
        </div>

        <form method="POST" id="loginForm">
            <h3>Log In</h3>
            <input type="text" name="login_input" placeholder="Phone / Email / Username" required>
            <input type="password" name="login_pass" placeholder="Enter Password" required>
            <button type="submit" name="login" class="primary">Log In</button>
        </form>
    </div>

    <script src="index.js"></script>
</body>
</html>