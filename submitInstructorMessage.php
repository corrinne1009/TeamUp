<?php
// Start session to verify instructor identity
session_start();
$instructorId = $_SESSION['userId'] ?? null;
if (!$instructorId) die("Instructor not logged in.");

// Connect to the database
try {
    $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
$teamId = $_POST['teamId'] ?? null;
$messageBody = $_POST['messageBody'] ?? null;

if (!$teamId || !$messageBody) {
    die("Missing team ID or message content.");
}

try {
    $stmt = $db->prepare("
        INSERT INTO messages (message, teamId, status)
        VALUES (:message, :teamId, :status)
    ");

    $stmt->execute([
        ':message' => $messageBody,
        ':teamId' => $teamId,
        ':status' => 'unread'
    ]);

    // Redirect back to the dashboard or show success
    header("Location: InstructorPage.php?messageSent=1");
    exit;
} catch (PDOException $e) {
    die("Error sending message: " . $e->getMessage());
}