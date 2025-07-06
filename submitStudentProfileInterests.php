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

$interest1 = $_POST['interest1'] ?? null;
$interest2 = $_POST['interest2'] ?? null;
$interest3 = $_POST['interest3'] ?? null;

$stmt = $db->prepare("UPDATE Profile SET interest1 = ?, interest2 = ?, interest3 = ? WHERE userId = ?");
$stmt->execute([$interest1, $interest2, $interest3, $userId]);

header("Location: CreateStudentProfile_StudentTeamwork.php");
exit();
?>