<?php
// 🛠️ Inline AJAX handler for team member info
if (isset($_GET['teamId']) && isset($_GET['ajax'])) {
  header('Content-Type: application/json');

  try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $teamId = $_GET['teamId'];
    $stmt = $db->prepare("
      SELECT u.firstName, u.lastName
      FROM teammember tm
      JOIN User u ON tm.userId = u.userId
      WHERE tm.teamId = ?
    ");
    $stmt->execute([$teamId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['members' => $members]);
  } catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
  }
  exit;
}

session_start();
$instructorId = $_SESSION['userId'] ?? null;
if (!$instructorId) die("Instructor not logged in.");

try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

//Instructor Info
$nameQuery = $db->prepare("SELECT firstName, lastName FROM User WHERE userId = ?");
$nameQuery->execute([$instructorId]);
$instructor = $nameQuery->fetch(PDO::FETCH_ASSOC);

//Instructor's Courses
$courseQuery = $db->prepare("
  SELECT c.courseId, c.name, c.section,
         (SELECT COUNT(*) FROM teams WHERE courseId = c.courseId) AS teamCount
  FROM Course c
  WHERE c.userId = ?
  ORDER BY c.name, c.section
");
$courseQuery->execute([$instructorId]);
$courses = $courseQuery->fetchAll(PDO::FETCH_ASSOC);

//All Teams by Course
$teamStmt = $db->prepare("
  SELECT t.teamId, t.courseId, GROUP_CONCAT(tm.userId) AS members
  FROM teams t
  LEFT JOIN teammember tm ON t.teamId = tm.teamId
  WHERE t.courseId IN (
    SELECT courseId FROM Course WHERE userId = ?
  )
  GROUP BY t.teamId, t.courseId
");
$teamStmt->execute([$instructorId]);
$teamsByCourse = [];

while ($row = $teamStmt->fetch(PDO::FETCH_ASSOC)) {
  $teamsByCourse[$row['courseId']][] = [
    'teamId' => $row['teamId'],
    'members' => explode(',', $row['members'] ?? '')
  ];
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <title>Instructor Dashboard</title>
  <link rel="stylesheet" href="TeamUp.css">
</head>
<body>

<header>
  <?php include 'Navbar.php'; ?>
</header>

<div class="card-container">
  <div class="profile-grid">

    <!--Column 1: Instructor Info + Courses -->
    <div class="column column-1">
      <div class="card">
        <div class="profile-top">
          <img src="Profile_pictures/placeholder_photo.png" alt="Profile Picture" class="profile-pic" />
          <div class="student-info">
            <h2><?= htmlspecialchars($instructor['firstName'] . ' ' . $instructor['lastName']) ?></h2>
            <p>Instructor Dashboard</p>
            <a href="#" class="edit-link">Edit</a>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>Courses You Teach</h3>
        <ul class="course-list">
          <?php foreach ($courses as $course): ?>
            <li>
              <?= htmlspecialchars($course['name']) ?> — Section <?= htmlspecialchars($course['section']) ?>
              <?php if ($course['teamCount'] == 0): ?>
                <button class="create-teams-btn" onclick="openTeamSetupModal(<?= $course['courseId'] ?>)">Create Teams</button>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- 👥 Column 2: Teams per Course -->
    <div class="column column-2">
      <div class="card">
        <h3>Teams per Course</h3>
        <div id="team-icon-wrapper">
          <?php foreach ($courses as $course): ?>
            <div class="team-section">
              <h4><?= htmlspecialchars($course['name']) ?> – Sec <?= htmlspecialchars($course['section']) ?></h4>
              <?php if (!empty($teamsByCourse[$course['courseId']])): ?>
                <ul class="team-list">
                  <?php foreach ($teamsByCourse[$course['courseId']] as $team): ?>
                    <li>
                      <button class="team-btn" onclick="highlightTeam(<?= $team['teamId'] ?>)">
                        Team #<?= $team['teamId'] ?>
                      </button>
                      <span class="team-size"><?= count($team['members']) ?> members</span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p>No teams yet.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!--Column 3: Selected Team Info + Message -->
    <div class="column column-3">
      <div class="card">
        <h3>Selected Team Info</h3>
        <div id="team-detail-view">
          <p>No team selected yet.</p>
        </div>
      </div>

      <div class="card">
        <h4>Message Team</h4>
        <form method="post" action="SendMessageToTeam.php">
          <input type="hidden" name="teamId" id="selectedTeamId" value="">
          <textarea name="messageBody" placeholder="Write your message here..." rows="5" required></textarea>
          <button type="submit" class="btn-login">Send</button>
        </form>
      </div>
    </div>

  </div>
</div>

<!-- Modal to Create Teams -->
<div id="team-setup-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Create Teams</h3>
    <form method="post" action="generateTeams.php">
      <input type="hidden" name="courseId" id="modalCourseId" value="">

      <label for="numTeams">Number of Teams</label>
      <input type="number" name="numTeams" id="numTeams" min="1" required>

      <label>Criteria Priority</label>
      <select name="priority[]" multiple required>
        <option value="availability">Availability</option>
        <option value="skills">Skills</option>
        <option value="interests">Interests</option>
      </select>

      <button type="submit" class="btn-login">Generate Teams</button>
      <button type="button" onclick="closeTeamSetupModal()">Cancel</button>
    </form>
  </div>
</div>

<!--JavaScript for modal + team selection -->
<script>
function openTeamSetupModal(courseId) {
  document.getElementById("modalCourseId").value = courseId;
  document.getElementById("team-setup-modal").style.display = "block";
}

function closeTeamSetupModal() {
  document.getElementById("team-setup-modal").style.display = "none";
}

function highlightTeam(teamId) {
  document.getElementById("selectedTeamId").value = teamId;

  fetch(`InstructorPage.php?ajax=1&teamId=${teamId}`)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById("team-detail-view");
      if (data.members && data.members.length > 0) {
        container.innerHTML = `
          <h4>Team #${teamId}</h4>
          <ul class="team-members">
            ${data.members.map(m => `<li>${m.firstName} ${m.lastName}</li>`).join('')}
          </ul>
        `;
      } else {
        container.innerHTML = `<p>No members found for this team.</p>`;
      }
    })
    .catch(error => {
      console.error('Error fetching team info:', error);
      document.getElementById("team-detail-view").innerHTML = `<p>Could not load team members.</p>`;
    });
}
</script>

</body>
</html>