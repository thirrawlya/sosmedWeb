<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection with correct path
if (file_exists('../includes/db.php')) {
    include '../includes/db.php';
} elseif (file_exists('includes/db.php')) {
    include 'includes/db.php';
} else {
    die("Database file not found.");
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user profile information
$user_id = $_SESSION['user_id'];
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result) ?? [];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['username'];
    $newBio = $_POST['bio'];
    
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile);
        $profilePic = $_FILES["profile_pic"]["name"];
        $updateQuery = "UPDATE users SET username = ?, bio = ?, profile_pic = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sssi", $newUsername, $newBio, $profilePic, $user_id);
    } else {
        $updateQuery = "UPDATE users SET username = ?, bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "ssi", $newUsername, $newBio, $user_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: profile.php?user_id=$user_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Social Media</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="container bg-white p-6 rounded-lg shadow-md text-center relative">
        <button onclick="window.location.href='home.php'" class="absolute left-4 top-4 text-gray-600 hover:text-black">
            &#8592;
        </button>
        <img src="uploads/<?php echo $userData['profile_pic'] ?? 'default.png'; ?>" class="w-24 h-24 rounded-full mx-auto">
        <h2 class="text-2xl font-bold mt-2">@<?php echo htmlspecialchars($userData['username'] ?? 'Unknown'); ?></h2>
        <p class="text-gray-600"> <?php echo htmlspecialchars($userData['bio'] ?? 'No bio available.'); ?> </p>
        <div class="mt-4 flex flex-col items-center gap-4">
            <button onclick="document.getElementById('editModal').classList.remove('hidden')" class="bg-blue-500 text-white px-4 py-2 rounded">Edit Profile</button>
        </div>
    </div>

    <!-- Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-md w-96">
            <h2 class="text-xl font-bold mb-4">Edit Profile</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block font-bold">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block font-bold">Bio</label>
                    <textarea name="bio" class="w-full p-2 border rounded"> <?php echo htmlspecialchars($userData['bio'] ?? ''); ?> </textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-bold">Profile Picture</label>
                    <input type="file" name="profile_pic" class="w-full p-2 border rounded">
                </div>
                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="bg-red-500 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
