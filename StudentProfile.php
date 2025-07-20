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

$stmt = $db->prepare("
  SELECT U.firstName, U.lastName, P.major, P.graduationSemester, P.graduationYear, P.bio,
         P.interest1, P.interest2, P.interest3,
         P.q1Response, P.q2Response, P.q3Response, P.q4Response, P.q5Response
  FROM User U
  JOIN Profile P ON U.userId = P.userId
  WHERE U.userId = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$avatarFiles = glob("Profile_avatars/avatar*.png");
$index = crc32($userId) % count($avatarFiles); // pseudo-random but consistent
$assignedAvatar = $avatarFiles[$index];

// Redirect to bio setup if profile is missing or required fields are empty
if (
  !$profile ||
  empty($profile['major']) ||
  empty($profile['bio']) ||
  empty($profile['graduationSemester']) ||
  empty($profile['graduationYear'])
) {
  header("Location: CreateStudentProfile_StudentBio.php");
  exit;
}

// Fetch availability
$availabilityStmt = $db->prepare("
  SELECT dayOfWeek, timeBlock
  FROM Availability
  WHERE profileId = (
    SELECT profileId FROM Profile WHERE userId = ?
  )
");
$availabilityStmt->execute([$userId]);
$availabilityRows = $availabilityStmt->fetchAll(PDO::FETCH_ASSOC);

// Group availability by day
$grouped = [];
foreach ($availabilityRows as $row) {
  $day = strtolower($row['dayOfWeek']);
  $grouped[$day][] = $row['timeBlock'];
}

// Fetch team ID for the user (via TeamMember)
$teamIdStmt = $db->prepare("
  SELECT teamId FROM TeamMember WHERE userId = ?
");
$teamIdStmt->execute([$userId]);
$teamRow = $teamIdStmt->fetch(PDO::FETCH_ASSOC);
$teamId = is_array($teamRow) ? $teamRow['teamId'] : null;

// Fetch team members
$teamMembers = [];
if ($teamId) {
  $teamMembersStmt = $db->prepare("
    SELECT U.firstName, U.lastName
    FROM TeamMember TM
    JOIN User U ON TM.userId = U.userId
    WHERE TM.teamId = ? AND TM.userId != ?
  ");
  $teamMembersStmt->execute([$teamId, $userId]);
  $teamMembers = $teamMembersStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch team name (or assign placeholder if not available)
$teamName = "Your Assigned Team";
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team Up Profile</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
<body class="profile_page">
  <header>
    <?php include 'Navbar.php'; ?>
  </header>

  <div class="card-container">
    <div class="profile-grid">

      <!-- Column 1: Photo, Info, Bio -->
      <div class="column column-1">
        <div class="card">
          <div class="profile-top">
            <img src="<?= htmlspecialchars($assignedAvatar) ?>" alt="Profile Picture" class="profile-pic" />
            <div class="student-info">
              <h2><?= htmlspecialchars($profile['firstName'] . ' ' . $profile['lastName']) ?></h2>
              <p>Major: <?= htmlspecialchars($profile['major']) ?></p>
              <p>Graduation: <?= htmlspecialchars($profile['graduationSemester']) ?> <?= htmlspecialchars($profile['graduationYear']) ?></p>
              <h3>Bio</h3>
              <p><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
              
              <a href="TO DO" class="edit-link">Edit</a>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="profile-bottom">
          <h3>Instructor Messages</h3>
          <p>You have no messages</p>
          </div>
        </div>
      </div>

      <!-- Column 2: Dynamic Team Display -->
      <div class="column column-2">
        <div class="card">
          <h3>Assigned Team</h3>
          <?php if ($teamId): ?>
            <p><strong>Team:</strong><br><?= htmlspecialchars($teamName) ?></p>
            <p><strong>Members:</strong></p>
            <ul class="team-list">
              <?php foreach ($teamMembers as $member): ?>
              <li class="team-member">
                <img src="Profile_pictures/default_student.png" alt="<?= htmlspecialchars($member['firstName']) ?>" class="mini-pic" />
                <span class="member-name"><?= htmlspecialchars($member['firstName'] . ' ' . $member['lastName']) ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>You’re not assigned to a team yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Column 3: Interests + Availability -->
      <div class="column column-3">
        <div class="card">
          <div class="interests">
            <h3>Interests</h3>
            <a href="TO DO" class="edit-link">Edit</a>
            <div class="interest-icons">
              <?php
              $interests = [$profile['interest1'], $profile['interest2'], $profile['interest3']];
              foreach ($interests as $interest):
                if ($interest):
              ?>
              <button type="button" class="icon-button" disabled>
                <img src="TeamUp_icons/interests_<?= strtolower($interest) ?>.png" alt="<?= htmlspecialchars($interest) ?>" />
                <span class="icon-label"><?= htmlspecialchars($interest) ?></span>
              </button>
              <?php endif; endforeach; ?>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="availability">
            <h3>Availability</h3>
            <a href="TO DO" class="edit-link">Edit</a>
            <div class="availability-grid horizontal">
              <?php foreach ($grouped as $day => $times): ?>
              <div class="availability-row">
                <span class="day-label"><?= ucfirst($day) ?></span>
                <div class="time-tags">
                  <?php foreach ($times as $time): ?>
                    <span class="time-card"><?= htmlspecialchars($time) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <!-- Optional footer -->
  </footer>
</body>
</html>