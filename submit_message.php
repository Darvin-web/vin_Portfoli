<?php
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    header("Location: index.php?error=emptyfields");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=invalidemail");
    exit();
}

if (!isset($pdo)) {
    header("Location: index.php?error=dberror");
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, message, status, created_at) VALUES (?, ?, ?, 'unread', NOW())");
    $stmt->execute([$name, $email, $message]);
    header("Location: index.php?success=messagesent");
    exit();
} catch(PDOException $e) {
    header("Location: index.php?error=dberror");
    exit();
}
?>