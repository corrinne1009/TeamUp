<?php
session_start();

// Connect to database
try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Check session
$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
  die("User not authenticated.");
}

// Gather form inputs
$firstName         = $_POST['fname'] ?? '';
$lastName          = $_POST['lname'] ?? '';
$major             = $_POST['major'] ?? '';
$graduationSemester = $_POST['gradSemester'] ?? '';
$graduationYear     = $_POST['gradYear'] ?? '';
$bio               = $_POST['Bio'] ?? '';
$courseSelection   = $_POST['courseSelection'] ?? '';

// Split concatenated course value
$courseParts = explode('|', $courseSelection);
$courseName = trim($courseParts[0] ?? '');
$courseSection = trim($courseParts[1] ?? '');

// === Ensure Profile row exists ===
$checkProfile = $db->prepare("SELECT profileId FROM Profile WHERE userId = ?");
$checkProfile->execute([$userId]);
$profileExists = $checkProfile->fetch(PDO::FETCH_ASSOC);

if (!$profileExists) {
  $createProfile = $db->prepare("
    INSERT INTO Profile (userId, major, bio, graduationSemester, graduationYear)
    VALUES (?, ?, ?, ?, ?)
  ");
  $createProfile->execute([$userId, $major, $bio, $graduationSemester, $graduationYear]);
} else {
  $updateProfile = $db->prepare("
    UPDATE Profile 
    SET major = ?, bio = ?, graduationSemester = ?, graduationYear = ? 
    WHERE userId = ?
  ");
  $updateProfile->execute([$major, $bio, $graduationSemester, $graduationYear, $userId]);
}

// === Handle course enrollment ===
if ($courseName && $courseSection) {
  // Find existing course from instructors
  $findCourse = $db->prepare("
    SELECT courseId FROM Course 
    WHERE name = ? AND section = ?
  ");
  $findCourse->execute([$courseName, $courseSection]);
  $course = $findCourse->fetch(PDO::FETCH_ASSOC);

  if ($course) {
    $courseId = $course['courseId'];

    // Check for existing enrollment
    $checkEnrollment = $db->prepare("
      SELECT enrollmentId FROM Enrollments 
      WHERE userId = ? AND courseId = ?
    ");
    $checkEnrollment->execute([$userId, $courseId]);
    $enrolled = $checkEnrollment->fetch(PDO::FETCH_ASSOC);

    if (!$enrolled) {
      $enroll = $db->prepare("INSERT INTO Enrollments (userId, courseId) VALUES (?, ?)");
      $enroll->execute([$userId, $courseId]);
    }
  }
}

// ✅ Redirect to Interests step
header("Location: CreateStudentProfile_StudentInterests.php");
exit;
?>