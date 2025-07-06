<?php
session_start();

// 🚪 Connect to MySQL
try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ✉️ Collect form input
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$role = $_POST['role'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$status = 'active'; // default status for new accounts

try {
    $db->beginTransaction();

    // ✅ Step 1: Create account
    $stmt1 = $db->prepare("INSERT INTO Account (email, password, status) VALUES (?, ?, ?)");
    $stmt1->execute([$email, $password, $status]);
    $accountId = $db->lastInsertId();

    // ✅ Step 2: Create user record
    $stmt2 = $db->prepare("INSERT INTO User (accountId, firstName, lastName, role) VALUES (?, ?, ?, ?)");
    $stmt2->execute([$accountId, $firstName, $lastName, $role]);

    $db->commit();

    // ✅ Step 3: Redirect to login
    header("Location: Login.html");
    exit();

} catch (PDOException $e) {
    $db->rollBack();
    echo "Registration failed: " . htmlspecialchars($e->getMessage());
}
?>