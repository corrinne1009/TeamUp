<?php
session_start();

// Basic security check
$instructorId = $_SESSION['userId'] ?? null;
if (!$instructorId) {
  die("Instructor not logged in.");
}

try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Fetch instructor's current courses
$courseQuery = $db->prepare("SELECT name, section FROM Course WHERE userId = ? ORDER BY name, section");
$courseQuery->execute([$instructorId]);
$assignedCourses = $courseQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Instructor Dashboard</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
<body>

<header>
  <?php include 'Navbar.php'; ?>
</header>

<div class="card-container">
  <div class="card layout">

    <!-- 🌵 Sidebar Navigation -->
    <nav class="sidebar">
      <ul>
        <li><a href="#" class="active">Courses</a></li>
        <!-- Add more sections like "Groups", "Students", etc. -->
      </ul>
    </nav>

    <!-- 📋 Main Content -->
    <main class="content">
      <h2>Your Teaching Dashboard</h2>

      <?php if (count($assignedCourses) > 0): ?>
        <ul class="existing-course-list">
          <?php foreach ($assignedCourses as $course): ?>
            <li><?= htmlspecialchars($course['name']) ?> — Section <?= htmlspecialchars($course['section']) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You haven’t added any courses yet. Fill out the form below to get started!</p>
      <?php endif; ?>

      <hr>

      <form action="submitInstructorCourses.php" method="post" class="setup-form">
        <h3>Add More Courses</h3>
        <div id="course-wrapper">
          <div class="form-row course-set">
            <div class="form-group">
              <label for="course[]">Course</label>
              <select name="course[]" class="form-control">
                <option value="">Select a course</option>
                <option value="ICS 311 - Database Management Systems">ICS 311 - Database Management Systems</option>
                <option value="ICS 340 - Algorithm Design Analysis">ICS 340 - Algorithm Design Analysis</option>
                <option value="ICS 370 - Software Design Models">ICS 370 - Software Design Models</option>
              </select>
            </div>

            <div class="form-group">
              <label for="section[]">Section</label>
              <select name="section[]" class="form-control">
                <option value="">Select a section</option>
                <option value="001">001</option>
                <option value="002">002</option>
                <option value="003">003</option>
              </select>
            </div>

            <button type="button" class="remove-course" style="display: none;">Remove</button>
          </div>
        </div>

        <button type="button" id="add-course" class="btn-login">Add course</button>
        <button type="submit" class="btn-login">Save Courses</button>
      </form>
    </main>

  </div>
</div>

<footer>
  <p>&copy; 2025 TeamUp. All rights reserved.</p>
</footer>

<script>
function updateRemoveButtons() {
  const sets = document.querySelectorAll('.course-set');
  const showRemove = sets.length > 1;
  sets.forEach(set => {
    const btn = set.querySelector('.remove-course');
    if (btn) {
      btn.style.display = showRemove ? 'inline-block' : 'none';
    }
  });
}

document.getElementById('add-course').addEventListener('click', function () {
  const wrapper = document.getElementById('course-wrapper');
  const clone = wrapper.firstElementChild.cloneNode(true);
  clone.querySelectorAll('select').forEach(select => select.value = '');
  wrapper.appendChild(clone);
  updateRemoveButtons();
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('remove-course')) {
    e.target.parentElement.remove();
    updateRemoveButtons();
  }
});

updateRemoveButtons();
</script>

</body>
</html>