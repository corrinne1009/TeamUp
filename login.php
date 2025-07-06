<?php
session_start();

// 🚪 Connect to MySQL (using XAMPP default credentials)
try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 📨 Get credentials from login form
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// ✅ Step 1: Check if the account exists and is active
$stmt = $db->prepare("SELECT accountId, password FROM Account WHERE email = ? AND status = 'active'");
$stmt->execute([$email]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    die("Login failed: invalid email or inactive account.");
}

//Step 2: Verify password
if ($password !== $account['password']) {
    die("Login failed: incorrect password.");
}

$accountId = $account['accountId'];

// Step 3: Fetch user role and profile ID
$stmt = $db->prepare("
    SELECT user.userId, user.role, profile.profileId
    FROM user
    LEFT JOIN profile on user.userId = profile.userId
    WHERE user.accountId = ?
"); 
$stmt->execute([$accountId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//exit();

if (!$user) {
    die("Login failed: no user found for this account.");
}

//Store essentials in session
$_SESSION['accountId'] = $accountId;
$_SESSION['userId'] = $user['userId'];
$_SESSION['role'] = $user['role'];

// 🚦 Step 4: Redirect based on role and profile status
if ($user['role'] === 'student') {
    if (!isset($user['profileId']) || $user['profileId'] === null) {
        header("Location: CreateStudentProfile_StudentBio.php");
    } else {
        header("Location: studentProfile.html");
    }
    exit();

} elseif ($user['role'] === 'instructor') {
    header("Location: instructorPage.html");
    exit();
} else {
    die("Unknown role. Please contact support.");
}
?>