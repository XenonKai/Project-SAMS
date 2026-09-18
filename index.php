<?php
session_start();
require 'db.php';

$destinations = ['admin' => 'admin.php', 'student' => 'student.php', 'faculty' => 'faculty.php'];

if (!empty($_SESSION['role']) && isset($destinations[$_SESSION['role']])) {
    header('Location: ' . $destinations[$_SESSION['role']]);
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$login_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    $input = trim($_POST['login_input'] ?? '');
    $password = $_POST['login_pass'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
        $login_message = 'Security validation failed. Please try again.';
    } elseif ($input === '' || $password === '') {
        $login_message = 'Please enter your email/phone and password.';
    } else {
        $sql = $conn->prepare('SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1');
        $sql->bind_param('ss', $input, $input);
        $sql->execute();
        $user = $sql->get_result()->fetch_assoc();
        $sql->close();

        $storedPassword = (string)($user['password'] ?? '');
        $validPassword = password_verify($password, $storedPassword);

        // Temporary compatibility for the demo admin account used in local testing.
        if (!$validPassword && $user && $user['phone'] === '09161196693' && $storedPassword === $password) {
            $validPassword = true;
        }

        if (!$user || !$validPassword) {
            $login_message = 'Invalid email/phone or password.';
        } elseif ($user['status'] !== 'Registered') {
            $login_message = 'Your account is not registered yet. Contact the administrator.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];
            header('Location: ' . ($destinations[$user['role']] ?? 'index.php'));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS - ACLC College of Malolos</title>
    <link rel="stylesheet" href="index.css?v=4">
</head>
<body>
    <div class="header">
        <h1>ACLC College of Malolos</h1>
        <h3><span>SAMS</span> / Student Attendance Management System</h3>
    </div>
    <div class="container">
        <?php if ($login_message !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($login_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <h3>Log In</h3>
            <p class="login-note">Accounts are created and managed by the administrator.</p>
            <input type="text" name="login_input" placeholder="Phone or Email" required>
            <input type="password" name="login_pass" placeholder="Password" required>
            <button type="submit" name="login" class="primary">Log In</button>
        </form>
    </div>
</body>
</html>
