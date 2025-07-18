<?php
session_start();
//Connect to MySQL 
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

    // Check if email exists
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
<html>
<head>
  <meta charset="UTF-8">
  <title>Reset Your Password</title>
  <link rel="stylesheet" href="TeamUp.css">
</head>
<body>
  <div class="login-container">
    <header>
      <h1>Reset Password</h1>
      <p>Enter your email and a new password</p>
    </header>

    <form class="login-form" method="POST" action="ForgotPassword.php">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required />
      </div>

      <div class="form-group">
        <label for="newPassword">New Password</label>
        <input type="password" name="newPassword" id="newPassword" required />
      </div>

      <?php if ($errorMessage): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
      <?php endif; ?>

      <button type="submit" class="btn-login">Reset Password</button>
      <div class="form-footer">
        <a href="Login.html">Return to login</a>
      </div>
    </form>
  </div>
</body>
</html>