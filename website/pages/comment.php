<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $post_id, $comment);
        if ($stmt->execute()) {
            header("Location: home.php");
            exit();
        } else {
            echo "Gagal menambahkan komentar!";
        }
    }
}
?>
