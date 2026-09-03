<?php
include 'db.php';
session_start();
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
  <div style="text-align:center; margin-bottom:10px;">
    <button onclick="showLogin()">Log In</button>
    <button onclick="showSignup()">Sign Up</button>
  </div>

  <!-- ROLE SELECTION -->
  <h3 style="text-align:center;">Select Role to Create Account</h3>
  <div class="role-cards">
    <div class="role-card" id="card-student" onclick="selectRole('student')">👨‍🎓<br><b>Student</b></div>
    <div class="role-card" id="card-faculty" onclick="selectRole('faculty')">👨‍🏫<br><b>Faculty</b></div>
    <div class="role-card" id="card-admin" onclick="selectRole('admin')">👨‍💼<br><b>Admin</b></div>
  </div>

  <!-- SIGN UP -->
  <form method="POST" id="signupForm" class="hidden">
    <h3>Create Account - <span id="roleLabel"></span></h3>
    <input type="hidden" name="role" id="roleInput" required>
    <input type="text" name="full_name" id="fullName" placeholder="Full Name" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <input type="email" name="email" placeholder="Email / Username" required>
    
    <div style="display:flex; gap:5px;">
      <input type="password" name="password" id="pass" placeholder="Create Password" required style="flex:1;">
      <button type="button" onclick="togglePass()" style="width:80px;">Show</button>
    </div>

    <div id="studentFields" class="hidden">
      <input type="text" name="student_id" placeholder="Student ID (from Admin)">
      <select name="course"><option value="">Select Course/Program</option>
      <option>STEM</option>
      <option>HUMSS</option>
      <option>ABM</option>
      <option>ICT-Programming</option>
      <option>ICT-Animation</option>
      <option>ICT-CSS</option>
      <option>BSEN</option>
      <option>ACT</option>
      <option>BSAIS</option>
      <option>BSCS</option></select>

      <select name="year_level"><option value="">Select Grade/Year Level</option>
      <option>Grade 11</option>
      <option>Grade 12</option>
      <option>1st Year</option>
      <option>2nd Year</option>
      <option>3rd Year</option>
      <option>4th Year</option></select>
    </div>

    <div id="facultyFields" class="hidden">
      <input type="text" name="faculty_id" placeholder="Faculty ID (from Admin)">
      <select name="department"><option value="">Select Department</option>
      <option>Science and Tech</option>
      <option>Arts and Humanities</option>
      <option>Social and Business Sciences</option>
      <option>Professional Fields</option></select>
    </div>
    
    <div id="adminFields" class="hidden">
      <input type="text" name="admin_id" placeholder="Admin ID">
      <p class="warning">⚠️ Never share admin access!</p>
    </div>

    <button type="submit" name="signup" class="primary">Create Account</button>
    <p style="text-align:center;">Direct Sign Up (editable full name)<br>
      <button type="button" class="google-btn">Continue with Google</button>
      <button type="button" class="fb-btn">Continue with Facebook</button>
      <button type="button">Continue with Email</button>
    </p>
  </form>

  <!-- LOG IN -->
  <form method="POST" id="loginForm">
    <h3>Log In</h3>
    <input type="text" name="login_input" placeholder="Phone / Email / Username" required>
    <input type="password" name="login_pass" placeholder="Enter Password" required>
    <button type="submit" name="login" class="primary">Log In</button>
    <p style="text-align:center;">Direct Log In<br>
      <button type="button" class="google-btn">Google</button>
      <button type="button" class="fb-btn">Facebook</button>
      <button type="button">Email</button>
    </p>
  </form>

  <?php
  if(isset($_POST['signup'])){
    $role = $_POST['role']; $full_name = $_POST['full_name']; $phone = $_POST['phone']; $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (role, full_name, phone, email, password, student_id, course, year_level, faculty_id, department, admin_id)
            VALUES ('$role','$full_name','$phone','$email','$password','".$_POST['student_id']."','".$_POST['course']."','".$_POST['year_level']."','".$_POST['faculty_id']."','".$_POST['department']."','".$_POST['admin_id']."')";
    if($conn->query($sql)){ echo "<p style='color:green;'>Account created! You can now log in.</p>"; }
      else { echo "<p style='color:red;'>Error: ".$conn->error."</p>"; }
  }
  if(isset($_POST['login'])){
    $input = $_POST['login_input']; $pass = $_POST['login_pass'];
    $result = $conn->query("SELECT * FROM users WHERE email='$input' OR phone='$input' OR username='$input'");
    if($result->num_rows > 0){
      $row = $result->fetch_assoc();
      if(password_verify($pass, $row['password'])){
        $_SESSION['role'] = $row['role']; $_SESSION['name'] = $row['full_name'];
        if($row['role']=='student') header("Location: student.php");
        if($row['role']=='faculty') header("Location: faculty.php");
        if($row['role']=='admin') header("Location: admin.php");
      } else { echo "<p style='color:red;'>Wrong password</p>"; }
    } else { echo "<p style='color:red;'>User not found</p>"; }
  }
  ?>
</div>

<script src="index.js"></script>
</body>
</html>
