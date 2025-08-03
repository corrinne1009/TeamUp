<?php
header('Content-Type: application/json');
$courseId = $_GET['courseId'] ?? null;

try {
  $db = new PDO("mysql:host=localhost;dbname=teamup;charset=utf8", "root", "");
  $stmt = $db->prepare("SELECT COUNT(*) AS count FROM enrollments WHERE courseId = ?");
  $stmt->execute([$courseId]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode(['count' => $result['count'] ?? 0]);
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
}