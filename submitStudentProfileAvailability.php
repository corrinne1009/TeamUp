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

// Get profileId for this user
$stmt = $db->prepare("SELECT profileId FROM Profile WHERE userId = ?");
$stmt->execute([$userId]);
$profileId = $stmt->fetchColumn();

if (!$profileId) {
  die("Profile not found.");
}

// Wipe existing availability to avoid duplicates
$db->prepare("DELETE FROM Availability WHERE profileId = ?")->execute([$profileId]);

// Loop through all 28 slots
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$times = ['morning','afternoon','evening','night'];

$insertStmt = $db->prepare("
  INSERT INTO Availability (profileId, dayOfWeek, timeBlock)
  VALUES (?, ?, ?)
");

foreach ($days as $day) {
  foreach ($times as $time) {
    $field = "{$day}-{$time}";
    if (isset($_POST[$field])) {
      $insertStmt->execute([$profileId, ucfirst($day), ucfirst($time)]);
    }
  }
}

header("Location: StudentProfile.php");
exit();
?>