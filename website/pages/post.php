<?php
// post.php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $content = !empty($_POST['content']) ? $_POST['content'] : NULL;
    $caption = !empty($_POST['caption']) ? $_POST['caption'] : NULL;
    $image = NULL;

    // Periksa apakah direktori penyimpanan ada
    $target_dir = "../assets/images/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Upload gambar jika ada
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            die("Error uploading image");
        }
    }

    // Simpan postingan ke database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, caption, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $content, $image, $caption);
    if ($stmt->execute()) {
        header("Location: home.php");
    } else {
        die("Error: " . $stmt->error);
    }
    exit();
}
?>