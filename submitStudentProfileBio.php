<?php
session_start();

try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
  die("User not logged in.");
}

$major = $_POST['major'] ?? '';
$gradSemester = $_POST['gradSemester'] ?? '';
$gradYear = $_POST['gradYear'] ?? '';
$bio = $_POST['Bio'] ?? '';

$stmt = $db->prepare("
  INSERT INTO Profile (
    userId, major, graduationSemester, graduationYear, bio
  ) VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$userId, $major, $gradSemester, $gradYear, $bio]);

// Redirect to the next step
header("Location: CreateStudentProfile_StudentInterests.php");
exit();
?>