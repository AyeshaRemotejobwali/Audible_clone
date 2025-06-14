<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || !isset($_POST['save_progress']) || !isset($_POST['id'])) {
    http_response_code(403);
    exit;
}
$user_id = $_SESSION['user_id'];
$book_id = $_POST['id'];
$current_time = (int)$_POST['current_time'];
$stmt = $pdo->prepare("INSERT INTO progress (user_id, audiobook_id, current_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE current_time = ?, updated_at = NOW()");
$stmt->execute([$user_id, $book_id, $current_time, $current_time]);
http_response_code(200);
?>
