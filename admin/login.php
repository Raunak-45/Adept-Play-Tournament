<?php
session_start();
require_once '../common/config.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">All fields are required.</div>';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Invalid password.</div>';
            }
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Invalid username.</div>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-user-select: none; user-select: none; }
    </style>
</head>
<body class="bg-gray-900 text-white flex flex-col items-center justify-center min-h-screen p-4 overflow-hidden">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Admin Panel</h1>
        </div>
        
        <?php echo $message; ?>

        <div class="bg-gray-800 rounded-lg p-8 shadow-lg">
             <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block mb-2 text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                    <input type="password" id="password" name="password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-colors">Login</button>
            </form>
        </div>
         <p class="text-center mt-4 text-sm text-gray-400">Back to <a href="../login.php" class="text-indigo-400 hover:underline">User Login</a></p>
    </div>
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
    </script>
</body>
</html>