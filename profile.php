<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'common/config.php';
$user_id = $_SESSION['user_id'];
$message = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (empty($username) || empty($email)) {
        $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Username and email are required.</div>';
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        if ($stmt->execute()) {
            $_SESSION['username'] = $username; // Update session username
            $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Profile updated successfully.</div>';
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error updating profile. The username or email might already be taken.</div>';
        }
        $stmt->close();
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">New passwords do not match.</div>';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            if($update_stmt->execute()){
                 $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Password changed successfully.</div>';
            } else {
                 $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error changing password.</div>';
            }
            $update_stmt->close();
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Incorrect current password.</div>';
        }
        $stmt->close();
    }
}

include 'common/header.php';

// Fetch user data
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>
    
    <?php echo $message; ?>

    <!-- Edit Profile Form -->
    <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Edit Profile</h2>
        <form action="profile.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block mb-2 text-sm font-medium">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <button type="submit" name="update_profile" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-colors">Update Profile</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Change Password</h2>
        <form action="profile.php" method="POST">
            <div class="mb-4">
                <label for="current_password" class="block mb-2 text-sm font-medium">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
             <div class="mb-4">
                <label for="new_password" class="block mb-2 text-sm font-medium">New Password</label>
                <input type="password" id="new_password" name="new_password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
             <div class="mb-4">
                <label for="confirm_password" class="block mb-2 text-sm font-medium">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <button type="submit" name="change_password" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 rounded-lg transition-colors">Change Password</button>
        </form>
    </div>
    
    <!-- Logout Button -->
     <a href="common/logout.php" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors">
        <i class="fas fa-sign-out-alt mr-2"></i>Logout
     </a>
</main>

<?php include 'common/bottom.php'; ?>