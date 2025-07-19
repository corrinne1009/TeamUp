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
?>


  <div class="card-container">
    <div class="card layout">
      <nav class="sidebar">
        <ul>
          <li><a href="#">Bio</a></li>
          <li><a href="#">Interests</a></li>
          <li><a href="#">Skills</a></li>
          <li><a href="#">Availability</a></li>
        </ul>
      </nav>

      <main class="content">
        <form action="submitStudentProfileBio.php" method="post">
          <div class="form-row">
            <div class="form-group">
              <label for="fname">First Name</label>
              <input type="text" id="fname" name="fname" value="<?= $firstName ?>" />
            </div>
            <div class="form-group">
              <label for="lname">Last Name</label>
              <input type="text" id="lname" name="lname" value="<?= $lastName ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="major">Major</label>
              <select id="major" name="major">
                <option value=""> </option>
                <option value="Computer Science">Computer Science (CS)</option>
                <option value="Computer Information Technology">Computer Information Technology (ICS)</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="gradSemester">Graduation Semester</label>
              <select id="gradSemester" name="gradSemester">
                <option value=""> </option>
                <option value="spring">Spring</option>
                <option value="summer">Summer</option>
                <option value="fall">Fall</option>
              </select>
            </div>

            <div class="form-group">
              <label for="gradYear">Graduation Year</label>
              <select id="gradYear" name="gradYear">
                <option value=""> </option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
                <option value="2027">2027</option>
                <option value="2028">2028</option>
              </select><br><br>
            </div>
          </div>

          <div class="form-group">
            <textarea id="Bio" name="Bio" rows="10" cols="90"
              placeholder="Coding wizard? Spreadsheet sorcerer? Snack supplier? Tell us who you are, what you're into, and why your future teammates will love working with you. Don’t be shy—we’re building dream teams here!"></textarea>
          </div>

          <div class="navigation-buttons">
            <button class="NavButton" type="submit">Next</button>
          </div>
        </form>
      </main>
    </div>
  </div>

  <footer>
    <!-- Optional footer content -->
  </footer>
</body>
</html>