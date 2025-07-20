<?php
session_start();

// Connect to MySQL
try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$errorMessage = '';
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = $_POST['email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';

    $stmt = $db->prepare("SELECT accountId FROM Account WHERE email = ?");
    $stmt->execute([$email]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE Account SET password = ? WHERE accountId = ?");
        $stmt->execute([$hashedPassword, $account['accountId']]);
        $successMessage = "Your password has been updated. You can now log in.";
    } else {
        $errorMessage = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Your Password</title>
  <link rel="stylesheet" href="TeamUp.css">
</head>
<body>

  <!-- Static Header -->
  <header class="main-header">
    <img src="Final team-up logo.png" alt="TeamUp Logo" class="logo" />
    <nav class="main-nav">
      <ul class="nav-list">
        <li><a href="HomePage.html">Home</a></li>
        <li><a href="aboutTeamUp.html">About</a></li>
      </ul>
    </nav>
  </header>

  <!-- Reset Form -->
  <div class="login-wrapper">
    <div class="login-container">
      <header>
        <h1>Reset Your Password</h1>
      </header>

      <form class="login-form" method="POST" action="ForgotPassword.php">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" placeholder="you@example.com" required />
        </div>

        <div class="form-group password-field">
  <label for="newPassword">New Password</label>
  <div class="password-wrapper">
    <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required />
    <span class="toggle-password" onclick="toggleVisibility('newPassword', this)">
      <!-- Eye open icon -->
      <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 4.5C7.305 4.5 3.215 7.633 1.5 12c1.715 4.367 5.805 7.5 10.5 7.5s8.785-3.133 10.5-7.5C20.785 7.633 16.695 4.5 12 4.5zM12 15c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/>
      </svg>
    </span>
  </div>
</div>

<div class="form-group password-field">
  <label for="confirmPassword">Confirm Password</label>
  <div class="password-wrapper">
    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required />
    <span class="toggle-password" onclick="toggleVisibility('confirmPassword', this)">
      <!-- Eye open icon (same as above initially) -->
      <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 4.5C7.305 4.5 3.215 7.633 1.5 12c1.715 4.367 5.805 7.5 10.5 7.5s8.785-3.133 10.5-7.5C20.785 7.633 16.695 4.5 12 4.5zM12 15c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/>
      </svg>
    </span>
  </div>
</div>


        <?php if ($errorMessage): ?>
          <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
          <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <button type="submit" class="btn-login">Reset Password</button>
        <div class="form-footer">
          <a href="HomePage.html">Return to login</a>
        </div>
      </form>
    </div>
  </div>
  <!-- Static Footer -->
  <footer>
    <p>&copy; 2025 TeamUp. All rights reserved.</p>
  </footer>
<script>
  function toggleVisibility(inputId, toggleIcon) {
    const input = document.getElementById(inputId);
    const svg = toggleIcon.querySelector('svg');
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    // Switch icon path
    svg.innerHTML = isPassword
      ? `<path d="M12 4.5C7.305 4.5 3.215 7.633 1.5 12c1.715 4.367 5.805 7.5 10.5 7.5s8.785-3.133 10.5-7.5C20.785 7.633 16.695 4.5 12 4.5zM12 15c-1.657 0-3-1.343-3-3s1.343-3 3-3"/>` // Eye open
      : `<path d="M2 12c1.718 4.367 5.805 7.5 10.5 7.5 1.859 0 3.627-.512 5.165-1.392l2.842 2.842 1.414-1.414L3.414 3.414 2 4.828l2.112 2.112C3.247 8.275 2.427 9.707 2 12z"/>`; // Eye closed
  }
</script>
</body>
</html>