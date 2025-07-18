<?php
session_start();

//Connect to MySQL
try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

//Collect form input
$firstName = $_POST['firstName'] ?? '';
$lastName  = $_POST['lastName']  ?? '';
$role      = $_POST['role']      ?? '';
$email     = $_POST['email']     ?? '';
$password  = $_POST['password']  ?? '';
$status    = 'active';
$errorMessage = '';

//Process form if POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    //Check for duplicate email
    $stmt = $db->prepare("SELECT COUNT(*) FROM Account WHERE email = ?");
    $stmt->execute([$email]);
    $emailCount = $stmt->fetchColumn();

    if ($emailCount > 0) {
        $errorMessage = "This email is already registered. Try logging in or use another address.";
    } else {
        try {
            $db->beginTransaction();

            // Step 1: Create account
            $stmt1 = $db->prepare("INSERT INTO Account (email, password, status) VALUES (?, ?, ?)");
            $stmt1->execute([$email, $hashedPassword, $status]);
            $accountId = $db->lastInsertId();

            // Step 2: Create user
            $stmt2 = $db->prepare("INSERT INTO User (accountId, firstName, lastName, role) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$accountId, $firstName, $lastName, $role]);

            $userId = $db->lastInsertId();

            $_SESSION['userId'] = $userId;
            $_SESSION['role'] = $role;

            $db->commit();

            // Step 3: Redirect based on role
if ($role === 'student') {
    header("Location: CreateStudentProfile_StudentBio.php");
    exit();
} elseif ($role === 'instructor') {
    header("Location: instructorPage.html");
    exit();
} else {
    // Optional fallback
    header("Location: Login.html");
    exit();
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
          <label>
            <input type="radio" name="role" value="student" <?= $role === 'student' ? 'checked' : '' ?> required />
            Student
          </label>
          <label>
            <input type="radio" name="role" value="instructor" <?= $role === 'instructor' ? 'checked' : '' ?> />
            Instructor
          </label>
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

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Create a secure password"
          required
        />
      </div>

      <button type="submit" class="btn-login">Sign Up</button>

      <div class="form-footer">
        <a href="Login.html">Already have an account?</a>
      </div>
    </form>
  </div>
</body>
</html>