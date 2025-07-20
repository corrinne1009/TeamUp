<?php
session_start();

// Confirm instructor session
$instructorId = $_SESSION['userId'] ?? null;
if (!$instructorId || $_SESSION['role'] !== 'instructor') {
  die("Access denied. Instructor not authenticated.");
}

// Connect to the database
try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Gather course and section inputs
$courses = $_POST['course'] ?? [];
$sections = $_POST['section'] ?? [];

for ($i = 0; $i < count($courses); $i++) {
  $courseName = trim($courses[$i]);
  $section    = trim($sections[$i]);

  // Skip incomplete or empty entries
  if ($courseName === '' || $section === '') {
    continue;
  }

  // Check if course already exists for this instructor
  $checkStmt = $db->prepare("
    SELECT courseId FROM Course 
    WHERE name = ? AND section = ? AND userId = ?
  ");
  $checkStmt->execute([$courseName, $section, $instructorId]);
  $existingCourse = $checkStmt->fetch(PDO::FETCH_ASSOC);

  // If not found, insert new course
  if (!$existingCourse) {
    $insertStmt = $db->prepare("
      INSERT INTO Course (name, section, userId) 
      VALUES (?, ?, ?)
    ");
    $insertStmt->execute([$courseName, $section, $instructorId]);
  }
}

//Redirect to instructor dashboard
header("Location: InstructorPage.php");
exit;
?>