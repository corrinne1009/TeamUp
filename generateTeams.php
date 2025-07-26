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

// Step 1: Build slot frequency and student availability map
$slotFrequency = [];
$studentSlots = [];

foreach ($studentsRaw as $student) {
  $userId = $student['userId'];
  $slots = explode(',', $student['availability']);
  $studentSlots[$userId] = $slots;

  foreach ($slots as $slot) {
    $slotFrequency[$slot] = ($slotFrequency[$slot] ?? 0) + 1;
  }
}

// Step 2: Choose top N anchor slots
arsort($slotFrequency);
$anchorSlots = array_slice(array_keys($slotFrequency), 0, $numberOfTeams);

// Step 3: Seed anchor-based groups
$anchorGroups = [];
$assigned = [];

foreach ($anchorSlots as $slot) {
  $anchorGroups[$slot] = [];
  foreach ($studentSlots as $userId => $slots) {
    if (in_array($slot, $slots) && !in_array($userId, $assigned)) {
      $anchorGroups[$slot][] = $userId;
      $assigned[] = $userId;
    }
  }
}

// Step 4: Normalize group sizes
$totalStudents = count($studentSlots);
$maxTeamSize = floor($totalStudents / $numberOfTeams);
$remainder = $totalStudents % $numberOfTeams;

// Fill anchor groups up to maxTeamSize
foreach ($anchorGroups as $slot => &$group) {
  foreach ($studentSlots as $userId => $slots) {
    if (count($group) >= $maxTeamSize) break;
    if (!in_array($userId, $assigned)) {
      $group[] = $userId;
      $assigned[] = $userId;
    }
  }
}

// Step 5: Assign remainders by best overlap
$stillUnassigned = array_diff(array_keys($studentSlots), $assigned);
foreach ($stillUnassigned as $userId) {
  $bestFit = null;
  $bestScore = -1;
  $slots = $studentSlots[$userId];

  foreach ($anchorGroups as $slot => $group) {
    $score = 0;
    foreach ($group as $memberId) {
      $overlap = array_intersect($slots, $studentSlots[$memberId]);
      $score += count($overlap);
    }
    if ($score > $bestScore) {
      $bestScore = $score;
      $bestFit = $slot;
    }
  }

  if ($bestFit && !in_array($userId, $anchorGroups[$bestFit])) {
    $anchorGroups[$bestFit][] = $userId;
    $assigned[] = $userId;
  }
}

// Final sanity check
$finalAssigned = array_merge(...array_values($anchorGroups));
$missing = array_diff(array_keys($studentSlots), $finalAssigned);

if (!empty($missing)) {
  // Fallback: assign remaining student(s) to smallest team
  foreach ($missing as $userId) {
    $smallestTeamKey = array_keys($anchorGroups)[0];
    $minSize = PHP_INT_MAX;

    foreach ($anchorGroups as $key => $group) {
      if (count($group) < $minSize) {
        $minSize = count($group);
        $smallestTeamKey = $key;
      }
    }

    if (!in_array($userId, $assigned)) {
      $anchorGroups[$smallestTeamKey][] = $userId;
      $assigned[] = $userId; 
    }
  }
}
$finalTeams = array_values($anchorGroups);

foreach ($finalTeams as $teamMembers) {
  $teamInsert = $db->prepare("INSERT INTO teams (courseId) VALUES (?)");
  $teamInsert->execute([$courseId]);
  $teamId = $db->lastInsertId();

  $memberInsert = $db->prepare("INSERT INTO teammember (teamId, userId) VALUES (?, ?)");
  foreach (array_unique($teamMembers) as $userId) {
    $memberInsert->execute([$teamId, $userId]);
  }
}

// Redirect back with confirmation flag
header("Location: InstructorPage.php?teamsGenerated=1");
exit;
?>