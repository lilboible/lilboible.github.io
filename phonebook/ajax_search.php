<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$query = $_GET['q'] ?? '';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$like = "%$query%";
$stmt = $pdo->prepare("SELECT CONCAT(last_name, ' ', first_name, ' ', patronymic) as full_name, department, phone FROM phonebook WHERE last_name LIKE ? OR first_name LIKE ? OR phone LIKE ? LIMIT 10");
$stmt->execute([$like, $like, $like]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results);
?>