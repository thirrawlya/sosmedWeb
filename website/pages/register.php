<?php
session_start();
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Cek apakah username atau email sudah digunakan
        $check_user = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_user->bind_param("ss", $username, $email);
        $check_user->execute();
        $check_user->store_result();
        
        if ($check_user->num_rows > 0) {
            $error = "Username atau Email sudah digunakan. Gunakan yang lain.";
        } else {
            // Enkripsi password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1"); // Redirect ke login dengan tanda sukses register
                exit();
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    } else {
        $error = "Harap isi semua kolom.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"> <?php echo $error; ?> </p>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
</body>
</html>
