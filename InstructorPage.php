<?php
//Inline AJAX handler for team member info
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

  <!--Navbar -->
  <header>
    <?php include 'Navbar.php'; ?>
  </header>

  <!--Main Container -->
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
          <h3>Courses with no teams</h3>
          <ul class="course-list">
            <?php foreach ($courses as $course): ?>
              <?php if ($course['teamCount'] == 0): ?>
                <li>
                  <?= htmlspecialchars($course['name']) ?> — Section <?= htmlspecialchars($course['section']) ?>
                  <button class="create-teams-btn" onclick="openTeamSetupModal(<?= $course['courseId'] ?>)">Create Teams</button>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!--Column 2: Teams per Course -->
      <div class="column column-2">
        <div class="card">
          <h3>Teams</h3>
          <div id="team-icon-wrapper">
           <?php foreach ($courses as $course): ?>
              <?php if (!empty($teamsByCourse[$course['courseId']])): ?>
                <div class="team-section">
                  <h4><?= htmlspecialchars($course['name']) ?> – Sec <?= htmlspecialchars($course['section']) ?></h4>
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
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!--Column 3: Selected Team Info + Messaging -->
      <div class="column column-3">
        <div class="card">
          <h3>Selected Team Info</h3>
          <div id="team-detail-view">
            <p>No team selected yet.</p>
          </div>
        </div>

        <div class="card">
          <h4>Message Team</h4>
          <form method="post" action="submitInstructorMessage.php">
            <input type="hidden" name="teamId" id="selectedTeamId" value="">
            <textarea name="messageBody" placeholder="Write your message here..." rows="5" required></textarea>
            <button type="submit" class="btn-login disabled-tip" id="sendMessageBtn" disabled title="You must select a team to send a message.">Send</button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <?php
  $studentCounts = [];
  $studentCountQuery = $db->prepare("
    SELECT courseId, COUNT(*) AS totalStudents
    FROM course
    GROUP BY courseId
      ");
  $studentCountQuery->execute();
  foreach ($studentCountQuery as $row) {
  $studentCounts[$row['courseId']] = $row['totalStudents'];
}
?>
  <!--Modal to Create Teams (Outside Grid Layout) -->
  <div id="team-setup-modal" class="modal" style="display: none;">
    <div class="modal-content">
      <h3>Create Teams</h3>
      <form method="post" action="generateTeams.php">
        <input type="hidden" name="courseId" id="modalCourseId" value="">
        <p id="student-count-display">Loading student count...</p>

        <label for="numberOfTeams">Number of Teams</label>
        <input type="number" name="numberOfTeams" id="numTeams" min="1" required>

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

  <!--JavaScript for Modal + Team Selection -->
  <script>
  function openTeamSetupModal(courseId) {
  document.getElementById("modalCourseId").value = courseId;
  document.getElementById("team-setup-modal").style.display = "block";
  document.body.classList.add("modal-open");

  fetch(`getStudentCount.php?courseId=${courseId}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById("student-count-display").textContent =
        `${data.count} student(s) are currently enrolled in this course.`;
    })
    .catch(err => {
      console.error(err);
      document.getElementById("student-count-display").textContent =
        `Couldn't load student count.`;
    });
}

// ✅ Move these out
function closeTeamSetupModal() {
  document.getElementById("team-setup-modal").style.display = "none";
  document.body.classList.remove("modal-open");
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
function highlightTeam(teamId) {
  document.getElementById("selectedTeamId").value = teamId;

  // Enable the send button after instructor selects a team to send a message to
  document.getElementById("sendMessageBtn").disabled = false;

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

