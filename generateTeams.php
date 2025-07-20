<?php
session_start();

// Verify instructor session
$instructorId = $_SESSION['userId'] ?? null;
if (!$instructorId || ($_SESSION['role'] ?? '') !== 'instructor') {
  die("Access denied. Only instructors can generate teams.");
}

// Connect to database
try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Get inputs
$courseId = $_POST['courseId'] ?? null;
$numberOfTeams = intval($_POST['numTeams'] ?? 0);
$priorityOrder = $_POST['priority'] ?? ['availability', 'skills', 'interests']; // fallback order
$priorityString = implode(',', $priorityOrder);

// Validate input
if (!$courseId || $numberOfTeams < 1) {
  die("Missing course or invalid number of teams.");
}

// Save criteria to teamcriteria table
$criteriaStmt = $db->prepare("
  INSERT INTO teamcriteria (courseId, numberOfTeams, priorityOrder)
  VALUES (?, ?, ?)
");
$criteriaStmt->execute([$courseId, $numberOfTeams, $priorityString]);

// Fetch students and their availability blocks
$studentStmt = $db->prepare("
  SELECT u.userId,
         GROUP_CONCAT(CONCAT(a.dayOfWeek, '-', a.timeBlock) SEPARATOR ',') AS availability
  FROM User u
  JOIN Enrollments e ON u.userId = e.userId
  JOIN Profile p ON u.userId = p.userId
  JOIN Availability a ON p.profileId = a.profileId
  WHERE e.courseId = ?
  GROUP BY u.userId
");
$studentStmt->execute([$courseId]);
$studentsRaw = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

// Group students by availability similarity
$availabilityMap = [];
foreach ($studentsRaw as $student) {
  $userId = $student['userId'];
  $raw = $student['availability'] ?? '';
  $slots = $raw !== '' ? explode(',', $raw) : [];

  // Sort and join to form a grouping key
  sort($slots);
  $slotKey = implode('|', array_unique($slots));

  if (!isset($availabilityMap[$slotKey])) {
    $availabilityMap[$slotKey] = [];
  }
  $availabilityMap[$slotKey][] = $userId;
}

// Flatten all users into one list for chunking
$flattened = [];
foreach ($availabilityMap as $group) {
  foreach ($group as $userId) {
    $flattened[] = $userId;
  }
}

// Divide into roughly equal team chunks
$teamChunks = array_chunk($flattened, ceil(count($flattened) / $numberOfTeams));

// Insert each team and its members
foreach ($teamChunks as $chunk) {
  $teamInsert = $db->prepare("INSERT INTO teams (courseId) VALUES (?)");
  $teamInsert->execute([$courseId]);
  $teamId = $db->lastInsertId();

  $memberInsert = $db->prepare("INSERT INTO teammember (teamId, userId) VALUES (?, ?)");
  foreach ($chunk as $userId) {
    $memberInsert->execute([$teamId, $userId]);
  }
}

// Redirect back with confirmation flag
header("Location: InstructorPage.php?teamsGenerated=1");
exit;
?>