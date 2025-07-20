<?php
session_start();

try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Get login credentials
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Step 1: Check account exists and is active
$stmt = $db->prepare("SELECT accountId, password FROM Account WHERE email = ? AND status = 'active'");
$stmt->execute([$email]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
  die("Login failed: invalid email or inactive account.");
}

// Step 2: Verify password
if (!password_verify($password, $account['password'])) {
  die("Login failed: incorrect password.");
}

$accountId = $account['accountId'];

// Step 3: Fetch user info
$stmt = $db->prepare("SELECT userId, role FROM User WHERE accountId = ?");
$stmt->execute([$accountId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("Login failed: no user found for this account.");
}

$userId = $user['userId'];
$_SESSION['accountId'] = $accountId;
$_SESSION['userId'] = $userId;
$_SESSION['role'] = $user['role'];

// Step 4: Role-based redirect
if ($user['role'] === 'student') {

  // Check profile completeness
  $stmt = $db->prepare("
    SELECT profileId, major, bio, graduationSemester, graduationYear
    FROM Profile
    WHERE userId = ?
  ");
  $stmt->execute([$userId]);
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);

  $profileIncomplete = (
    !$profile ||
    empty($profile['major']) ||
    empty($profile['bio']) ||
    empty($profile['graduationSemester']) ||
    empty($profile['graduationYear'])
  );

  if ($profileIncomplete) {
    header("Location: CreateStudentProfile_StudentBio.php");
    exit;
  } else {
    header("Location: studentProfile.php");
    exit;
  }

} elseif ($user['role'] === 'instructor') {
  // Check if instructor has any associated courses
  $courseCheck = $db->prepare("SELECT COUNT(*) FROM Course WHERE userId = ?");
  $courseCheck->execute([$userId]);
  $courseCount = $courseCheck->fetchColumn();

  if ($courseCount > 0) {
    header("Location: instructorPage.php");
  } else {
    header("Location: InstructorSetUpPage.php");
  }
  exit;
} else {
  die("Unknown role. Please contact support.");
}