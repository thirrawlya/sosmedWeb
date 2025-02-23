<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['post_id']) && isset($_GET['type'])) {
    $post_id = intval($_GET['post_id']);
    $type = $_GET['type'] === 'like' ? 'like' : 'dislike';

    // Cek apakah user sudah memberi like atau dislike sebelumnya
    $check = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $check->bind_param("ii", $user_id, $post_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada, update like/dislike
        $stmt = $conn->prepare("UPDATE likes SET type = ? WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("sii", $type, $user_id, $post_id);
    } else {
        // Jika belum, tambahkan like/dislike
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id, type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $post_id, $type);
    }

    $stmt->execute();
    header("Location: home.php");
    exit();
}

header("Location: home.php");
exit();
?>
