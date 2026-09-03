<?php
$conn = new mysqli("localhost", "root", "", "aclc_sams");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
