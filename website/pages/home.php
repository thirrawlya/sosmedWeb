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

// Fetch trending posts
$trendingQuery = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.likes DESC, posts.created_at DESC LIMIT 5";
$trendingResult = mysqli_query($conn, $trendingQuery);
$trendingPosts = mysqli_fetch_all($trendingResult, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Social Media</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark');
            let theme = document.body.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }
        });

        function toggleComments(postId) {
            let commentSection = document.getElementById('comments-' + postId);
            commentSection.classList.toggle('hidden');
        }

        let currentSlide = 0;
        function showSlide(index) {
            const slides = document.querySelectorAll('.trending-slide');
            const slider = document.getElementById('trending-slider');
            if (index >= slides.length) currentSlide = 0;
            if (index < 0) currentSlide = slides.length - 1;
            else currentSlide = index;
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        setInterval(() => {
            showSlide(currentSlide + 1);
        }, 5000);
    </script>
    <style>
        .dark {
            background-color: #1a202c;
            color: white;
        }
        .dark .bg-white {
            background-color: #2d3748;
            color: white;
        }
        .dark .text-gray-600 {
            color: #a0aec0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-md p-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Social Media</h1>
        <input type="text" placeholder="Search..." class="border rounded-lg p-2 w-1/3">
        <div>
        <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="theme-toggle" onclick="toggleTheme()" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-500 rounded-full peer peer-checked:after:translate-x-full after:absolute after:top-0.5 after:left-1 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
            </label>
            <a href="profile.php?user_id=<?php echo $user_id; ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Profile</a>
            <a href="logout.php" class="ml-2 bg-red-500 text-white px-4 py-2 rounded-lg">Logout</a>
        </div>
    </nav>
    
    <div class="flex mt-4 mx-8 space-x-6">
        <!-- Sidebar -->
        <aside class="w-1/4 bg-white p-4 shadow-md rounded-lg">
            <div class="text-center">
                <img src="uploads/<?php echo htmlspecialchars($userData['profile_pic'] ?? 'default.png'); ?>" class="w-24 h-24 rounded-full mx-auto">
                <h2 class="font-bold text-lg">@<a href="profile.php?user_id=<?php echo $user_id; ?>" class="text-blue-500"><?php echo htmlspecialchars($userData['username'] ?? 'Unknown'); ?></a></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($userData['bio'] ?? 'No bio available.'); ?></p>
            </div>
            <ul class="mt-4">
                <li class="p-2 hover:bg-gray-200 rounded"><a href="home.php">Home</a></li>
                <li class="p-2 hover:bg-gray-200 rounded"><a href="messages.php">Messages</a></li>
                <li class="p-2 hover:bg-gray-200 rounded"><a href="notifications.php">Notifications</a></li>
                <li class="p-2 hover:bg-gray-200 rounded"><a href="settings.php">Settings</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="w-1/2 mx-4">
            <div class="bg-white p-4 rounded-lg shadow-md mb-4">
                <form action="post_handler.php" method="POST" enctype="multipart/form-data">
                    <textarea name="post_content" placeholder="What's on your mind?" class="w-full p-2 border rounded"></textarea>
                    <input type="file" name="post_image" class="mt-2">
                    <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-lg">Post</button>
                </form>
            </div>
            
            <?php
            // Fetch posts from database
            $query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY likes DESC, created_at DESC";
            $result = mysqli_query($conn, $query);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='bg-white p-4 rounded-lg shadow-md mb-4'>";
                    echo "<h3 class='font-bold'><a href='profile.php?user_id=" . $row['user_id'] . "' class='text-blue-500'>@" . htmlspecialchars($row['username']) . "</a></h3>";
                    echo "<p class='text-gray-600'>" . htmlspecialchars($row['content']) . "</p>";
                    if (!empty($row['image'])) {
                        echo "<img src='uploads/" . htmlspecialchars($row['image']) . "' alt='Post Image' class='mt-2 rounded-lg'>";
                    }
                    echo "<div class='flex justify-between mt-2'>";
                    echo "<button class='text-blue-500'>Like (<span>" . ($row['likes'] ?? 0) . "</span>)</button>";
                    echo "<button class='text-red-500'>Dislike</button>";
                    echo "<button class='text-blue-500'>Comment</button>";
                    echo "<button class='text-blue-500'>Share</button>";
                    echo "</div></div>";
                }
            }
            ?>
        </main>
        
        <!-- Trending Content -->
        <aside class="w-1/4 bg-white p-4 shadow-md rounded-lg relative">
            <h2 class="font-bold text-lg mb-2">Trending Content</h2>
            <div class="overflow-hidden relative">
                <div class="flex transition-transform duration-500" id="trending-slider" style="width: 500%">
                    <?php foreach ($trendingPosts as $post): ?>
                        <div class="trending-slide w-full p-4 flex-none">
                            <h3 class="font-bold text-blue-500">@<?php echo htmlspecialchars($post['username']); ?></h3>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <span class="text-sm text-gray-600">Likes: <?php echo $post['likes']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-300 px-2 py-1 rounded" onclick="showSlide(currentSlide - 1)">❮</button>
                <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-300 px-2 py-1 rounded" onclick="showSlide(currentSlide + 1)">❯</button>
            </div>
        </aside>
    </div>
</body>
</html>
