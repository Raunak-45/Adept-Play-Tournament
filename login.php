<?php
session_start();
require_once 'common/config.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Signup Logic
    if (isset($_POST['signup'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($username) || empty($email) || empty($password)) {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">All fields are required.</div>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Invalid email format.</div>';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Signup successful! Please login.</div>';
            } else {
                $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }

    // Login Logic
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Username and password are required.</div>';
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    header("Location: index.php");
                    exit();
                } else {
                    $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Invalid password.</div>';
                }
            } else {
                $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">No user found with that username.</div>';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup - Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
    </style>
</head>
<body class="bg-gray-900 text-white flex flex-col items-center justify-center min-h-screen p-4 overflow-hidden">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold">Adept Play</h1>
        </div>
        
        <?php echo $message; ?>

        <div x-data="{ tab: 'login' }" class="bg-gray-800 rounded-lg p-2">
            <div class="flex border-b border-gray-700">
                <button @click="tab = 'login'" :class="{'bg-indigo-600': tab === 'login'}" class="flex-1 py-3 text-center font-semibold rounded-t-lg transition-colors">Login</button>
                <button @click="tab = 'signup'" :class="{'bg-indigo-600': tab === 'signup'}" class="flex-1 py-3 text-center font-semibold rounded-t-lg transition-colors">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div x-show="tab === 'login'" class="p-6">
                <form action="login.php" method="POST">
                    <div class="mb-4">
                        <label for="login_username" class="block mb-2 text-sm font-medium">Username</label>
                        <input type="text" id="login_username" name="username" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="login_password" class="block mb-2 text-sm font-medium">Password</label>
                        <input type="password" id="login_password" name="password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <button type="submit" name="login" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-colors">Login</button>
                </form>
            </div>

            <!-- Signup Form -->
            <div x-show="tab === 'signup'" class="p-6" style="display: none;">
                <form action="login.php" method="POST">
                    <div class="mb-4">
                        <label for="signup_username" class="block mb-2 text-sm font-medium">Username</label>
                        <input type="text" id="signup_username" name="username" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="signup_email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" id="signup_email" name="email" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="signup_password" class="block mb-2 text-sm font-medium">Password</label>
                        <input type="password" id="signup_password" name="password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <button type="submit" name="signup" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-colors">Sign Up</button>
                </form>
            </div>
        </div>
         <p class="text-center mt-4 text-sm text-gray-400">Admin Panel: <a href="admin/login.php" class="text-indigo-400 hover:underline">Admin Login</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('copy', event => event.preventDefault());
        document.addEventListener('cut', event => event.preventDefault());
        document.addEventListener('paste', event => event.preventDefault());
        
        // Disable zoom
        document.addEventListener('wheel', function(event) {
            if (event.ctrlKey) {
                event.preventDefault();
            }
        }, { passive: false });

        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && (event.key === '+' || event.key === '-' || event.key === '0')) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>