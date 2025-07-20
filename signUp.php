<?php
session_start();

// Connect to DB
try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Form input
$firstName = $_POST['firstName'] ?? '';
$lastName  = $_POST['lastName']  ?? '';
$role      = $_POST['role']      ?? '';
$email     = $_POST['email']     ?? '';
$password  = $_POST['password']  ?? '';
$errorMessage = '';
$status = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  // Check for duplicate email
  $emailCheck = $db->prepare("SELECT COUNT(*) FROM Account WHERE email = ?");
  $emailCheck->execute([$email]);

  if ($emailCheck->fetchColumn() > 0) {
    $errorMessage = "This email is already registered. Try logging in or use another address.";
  } else {
    try {
      $db->beginTransaction();

      // Create account
      $createAccount = $db->prepare("INSERT INTO Account (email, password, status) VALUES (?, ?, ?)");
      $createAccount->execute([$email, $hashedPassword, $status]);
      $accountId = $db->lastInsertId();

      // Create user profile
      $createUser = $db->prepare("INSERT INTO User (accountId, firstName, lastName, role) VALUES (?, ?, ?, ?)");
      $createUser->execute([$accountId, $firstName, $lastName, $role]);
      $userId = $db->lastInsertId();

      $db->commit();

      // Store session
      $_SESSION['userId'] = $userId;
      $_SESSION['role'] = $role;

      // 🎯 Role-based redirect
      if ($role === 'student') {
        header("Location: CreateStudentProfile_StudentBio.php");
        exit;
      if ($role === 'instructor') {
        header("Location: InstructorSetUpPage.php");
        exit;
      }
      } else {
        header("Location: HomePage.html");
        exit;
      }

    } catch (PDOException $e) {
      $db->rollBack();
      $errorMessage = "Registration failed: " . htmlspecialchars($e->getMessage());
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TeamUp – Sign Up</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
<body>

  <!-- Header -->
  <header class="main-header">
    <img src="Final team-up logo.png" alt="TeamUp Logo" class="logo" />
    <nav class="main-nav">
      <ul class="nav-list">
        <li><a href="HomePage.html">Home</a></li>
        <li><a href="aboutTeamUp.html">About</a></li>
      </ul>
    </nav>
  </header>

  <!-- Sign-Up Form -->
  <div class="login-wrapper">
    <div class="login-container">
      <header>
        <h1>Create Your TeamUp Account</h1>
        <p>Connect and collaborate from day one</p>
      </header>

      <form class="login-form" method="POST" action="SignUp.php">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input
            type="text"
            id="firstName"
            name="firstName"
            placeholder="Your first name"
            value="<?= htmlspecialchars($firstName) ?>"
            required
          />
        </div>

        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input
            type="text"
            id="lastName"
            name="lastName"
            placeholder="Your last name"
            value="<?= htmlspecialchars($lastName) ?>"
            required
          />
        </div>

        <div class="form-group">
          <label>Are you a:</label>
          <div class="role-options">
            <label><input type="radio" name="role" value="student" <?= $role === 'student' ? 'checked' : '' ?> required /> Student</label>
            <label><input type="radio" name="role" value="instructor" <?= $role === 'instructor' ? 'checked' : '' ?> />Instructor</label>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="you@example.com"
            value="<?= htmlspecialchars($email) ?>"
            required
          />
          <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group password-field">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Create a secure password"
              required
            />
            <span class="toggle-password" onclick="toggleVisibility('password', this)">
              <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 4.5C7.305 4.5 3.215 7.633 1.5 12c1.715 4.367 5.805 7.5 10.5 7.5s8.785-3.133 10.5-7.5C20.785 7.633 16.695 4.5 12 4.5zM12 15c-1.657 0-3-1.343-3-3s1.343-3 3-3"/>
              </svg>
            </span>
          </div>
        </div>

        <button type="submit" class="btn-login">Sign Up</button>

        <div class="form-footer">
          <a href="HomePage.html">Already have an account?</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 TeamUp. All rights reserved.</p>
  </footer>

  <script>
    function toggleVisibility(inputId, toggleIcon) {
      const input = document.getElementById(inputId);
      const svg = toggleIcon.querySelector('svg');
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      svg.innerHTML = isPassword
        ? `<path d="M12 4.5C7.305 4.5 3.215 7.633 1.5 12c1.715 4.367 5.805 7.5 10.5 7.5s8.785-3.133 10.5-7.5C20.785 7.633 16.695 4.5 12 4.5zM12 15c-1.657 0-3-1.343-3-3s1.343-3 3-3"/>`
        : `<path d="M2 12c1.718 4.367 5.805 7.5 10.5 7.5 1.859 0 3.627-.512 5.165-1.392l2.842 2.842 1.414-1.414L3.414 3.414 2 4.828l2.112 2.112C3.247 8.275 2.427 9.707 2 12z"/>`;
    }
  </script>

</body>
</html>