<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team Up</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
<body>
  
  <header>
    <?php include 'Navbar.php'; ?>
  </header>
  <?php
session_start();
try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

$userId = $_SESSION['userId'] ?? null;
$firstName = '';
$lastName = '';

if ($userId) {
  $stmt = $db->prepare("SELECT firstName, lastName FROM User WHERE userId = ?");
  $stmt->execute([$userId]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($user) {
    $firstName = htmlspecialchars($user['firstName']);
    $lastName = htmlspecialchars($user['lastName']);
  }
}

$courseListQuery = $db->prepare("
  SELECT name, section 
  FROM Course 
  ORDER BY name, section
");
$courseListQuery->execute();
$availableCourseSections = $courseListQuery->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="card-container">
  <div class="card layout"> <!-- This is the wrapper -->

    <!-- Left Sidebar -->
    <nav class="sidebar">
      <ul>
        <li><a href="#" class="active">Bio</a></li>
        <li><a href="#">Interests</a></li>
        <li><a href="#">Skills</a></li>
        <li><a href="#">Availability</a></li>
      </ul>
    </nav>

    <!-- Right Main Content -->
    <main class="content">
      <!-- Your form goes here -->
<form action="submitStudentProfileBio.php" method="post" class="login-form">
  
  <!-- First & Last Name -->
  <div class="form-row">
    <div class="form-group">
      <label for="fname">First Name</label>
      <input type="text" id="fname" name="fname" class="form-control" value="<?= $firstName ?>" />
    </div>
    <div class="form-group">
      <label for="lname">Last Name</label>
      <input type="text" id="lname" name="lname" class="form-control" value="<?= $lastName ?>" />
    </div>
  </div>

  <!-- Major -->
  <div class="form-group">
    <label for="major">Major</label>
    <select id="major" name="major" class="form-control">
      <option value="">Select your major</option>
      <option value="Computer Science">Computer Science (CS)</option>
      <option value="Computer Information Technology">Computer Information Technology (ICS)</option>
      <option value="Other">Other</option>
    </select>
  </div>

  <!-- Enrolled Class(es)-->
  <!-- To do: Make Classes multiselect -->
<div class="form-group">
  <label for="courseSelection">Course & Section</label>
  <select id="courseSelection" name="courseSelection" class="form-control">
    <option value="">Select your course</option>
    <?php foreach ($availableCourseSections as $course): 
      $value = $course['name'] . '|' . $course['section']; // used for backend parsing
      $display = $course['name'] . ' — Section ' . $course['section'];
    ?>
      <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($display) ?></option>
    <?php endforeach; ?>
  </select>
</div>

  <!-- Graduation Semester & Year -->
  <div class="form-row">
    <div class="form-group">
      <label for="gradSemester">Graduation Semester</label>
      <select id="gradSemester" name="gradSemester" class="form-control">
        <option value="">Choose semester</option>
        <option value="spring">Spring</option>
        <option value="summer">Summer</option>
        <option value="fall">Fall</option>
      </select>
    </div>
    <div class="form-group">
      <label for="gradYear">Graduation Year</label>
      <select id="gradYear" name="gradYear" class="form-control">
        <option value="">Choose year</option>
        <option value="2025">2025</option>
        <option value="2026">2026</option>
        <option value="2027">2027</option>
        <option value="2028">2028</option>
      </select>
    </div>
  </div>

  <!-- Personal Bio -->
  <div class="form-group">
    <label for="Bio">Personal Bio</label>
    <textarea
      id="Bio"
      name="Bio"
      class="form-control"
      rows="10"
      placeholder="Coding wizard? Spreadsheet sorcerer? Snack supplier? Tell us who you are, what you're into, and why your future teammates will love working with you. Don’t be shy—we’re building dream teams here!"></textarea>
  </div>

  <div class="navigation-buttons">
    <button class="NavButton" type="submit">Next</button>
  </div>
</form>

    </main>

  </div> <!-- End wrapper -->
</div>

  <footer>
    <!-- Optional footer content -->
  </footer>
</body>
</html>