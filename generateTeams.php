<?php
session_start();

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

//exit;

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
$numberOfTeams = intval($_POST['numberOfTeams'] ?? 0);
$priorityOrder = $_POST['priority'] ?? ['availability', 'skills', 'interests'];
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

$users = [];
foreach ($studentsRaw as $student) {
    $users[] = [
        'id' => $student['userId'],
        'availability' => explode(',', $student['availability'])
    ];
}

// Organize availability data
$availabilityMap = [];
foreach ($studentsRaw as $student) {
    $id = $student['userId'];
    $slots = explode(',', $student['availability']);
    foreach ($slots as $slot) {
        if (!isset($availabilityMap[$slot])) {
            $availabilityMap[$slot] = [];
        }
        $availabilityMap[$slot][] = $id;
    }
}

//create helper function that groups student who whare overlapping timeblocks
function findClusters($overlapMatrix, $threshold = 3) {
    $clusters = [];

    foreach ($overlapMatrix as $idA => $others) {
        $cluster = [$idA];

        foreach ($others as $idB => $count) {
            if ($count >= $threshold) {
                $cluster[] = $idB;
            }
        }

        if (count($cluster) >= 3) {
            sort($cluster);
            $key = implode('-', $cluster);
            $clusters[$key] = array_unique($cluster);
        }
    }

    return array_values($clusters);
}

//Create a matrix that compares each user to every other user, counting overlapping time blocks.

$seeded = [];
$teams = array_fill(0, $numberOfTeams, []);
$maxTeamSize = ceil(count($users) / $numberOfTeams);

// Assign first few strong clusters to teams
foreach ($users as $userA) {
    foreach ($users as $userB) {
        if ($userA['id'] === $userB['id']) continue;

        $overlapCount = count(array_intersect($userA['availability'], $userB['availability']));
        $overlapMatrix[$userA['id']][$userB['id']] = $overlapCount;
    }
}

$clusters = findClusters($overlapMatrix);

for ($i = 0; $i < count($clusters) && $i < $numberOfTeams; $i++) {
    foreach ($clusters[$i] as $userId) {
        if (count($teams[$i]) < $maxTeamSize && !in_array($userId, $seeded)) {
            $teams[$i][] = $userId;
            $seeded[] = $userId;
        }
    }
}

//initialize teams as empty arrays
$teams = array_fill(0, $numberOfTeams, []);
$maxTeamSize = ceil(count($users) / $numberOfTeams);
$assigned = $seeded;

foreach ($users as $user) {
    $bestTeam = null;
    $bestScore = -1;

    foreach ($teams as $index => $team) {
        // Skip full teams
        if (count($team) >= $maxTeamSize) continue;

        $score = 0;
        foreach ($team as $memberId) {
            $score += $overlapMatrix[$user['id']][$memberId] ?? 0;
        }

        if ($score > $bestScore) {
            $bestTeam = $index;
            $bestScore = $score;
        }
    }

    // Fallback: if no overlap or all teams full, find any team with space
    if ($bestTeam === null) {
        foreach ($teams as $i => $team) {
            if (count($team) < $maxTeamSize) {
                $bestTeam = $i;
                break;
            }
        }
    }

    // Final fallback in case everything's at max capacity (shouldn't happen unless rounding issues)
    if ($bestTeam === null) {
        $bestTeam = array_rand($teams);
    }

    $teams[$bestTeam][] = $user['id'];
    $assigned[] = $user['id'];
}

//Debug
//echo "<pre>";
//print_r($assigned);
//echo "</pre>";
//exit;


// Handle students without overlaps
$allIds = array_column($studentsRaw, 'userId');
$unassignedIds = array_diff($allIds, $assigned);

//echo "<pre>";
//print_r($unassignedIds);
//echo "</pre>";
//exit;

//Handle unassigned students
if (!empty($unassignedIds)) {
    $chunkSize = max(1, ceil(count($unassignedIds) / $numberOfTeams));
    $leftoverTeams = array_chunk($unassignedIds, $chunkSize);

    // Merge leftover students into existing clusters
    foreach ($leftoverTeams as $i => $leftovers) {
        if (!isset($teamClusters[$i])) {
            $teamClusters[$i] = [];
        }
        $teamClusters[$i] = array_merge($teamClusters[$i], $leftovers);
    }
} else {
    // No leftovers, so use original teams directly
    $teamClusters = $teams;
}

// Merge leftover students into existing clusters
foreach ($leftoverTeams as $i => $leftovers) {
    if (!isset($teamClusters[$i])) {
        $teamClusters[$i] = [];
    }
    $teamClusters[$i] = array_merge($teamClusters[$i], $leftovers);
}

// Save teams and assign members
foreach ($teamClusters as $teamIndex => $members) {
    // Create team record
    $teamStmt = $db->prepare("
        INSERT INTO teams (courseId)
        VALUES (?)
    ");
    $teamStmt->execute([$courseId]);
    $teamId = $db->lastInsertId();

    // Assign members
    $memberStmt = $db->prepare("
        INSERT INTO teammember (teamId, userId)
        VALUES (?, ?)
    ");
    foreach ($members as $userId) {
        $memberStmt->execute([$teamId, $userId]);
    }
}

// Redirect to instructor dashboard
header("Location: InstructorPage.php");
exit;
?>