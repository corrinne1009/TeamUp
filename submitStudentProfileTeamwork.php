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

$q1 = $_POST['q1'] ?? null;
$q2 = $_POST['q2'] ?? null;
$q3 = $_POST['q3'] ?? null;
$q4 = $_POST['q4'] ?? null;
$q5 = $_POST['q5'] ?? null;

$stmt = $db->prepare("
  UPDATE Profile
  SET q1Response = ?, q2Response = ?, q3Response = ?, q4Response = ?, q5Response = ?
  WHERE userId = ?
");
$stmt->execute([$q1, $q2, $q3, $q4, $q5, $userId]);

header("Location: CreateStudentProfile_StudentAvailability.php");
exit();
?>