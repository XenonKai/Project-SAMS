CREATE DATABASE aclc_sams;
USE aclc_sams;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role VARCHAR(20),
  full_name VARCHAR(100),
  phone VARCHAR(20),
  email VARCHAR(100) UNIQUE,
  username VARCHAR(100),
  password VARCHAR(255),
  student_id VARCHAR(50) NULL,
  course VARCHAR(50) NULL,
  year_level VARCHAR(20) NULL,
  faculty_id VARCHAR(50) NULL,
  department VARCHAR(100) NULL,
  admin_id VARCHAR(50) NULL
);