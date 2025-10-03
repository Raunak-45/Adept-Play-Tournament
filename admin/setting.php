<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../common/config.php';
$message = '';
$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Update username
    $stmt_user = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
    $stmt_user->bind_param("si", $username, $admin_id);
    if($stmt_user->execute()){
        $_SESSION['admin_username'] = $username;
        $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Admin username updated successfully.</div>';
    } else {
        $message .= '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error updating username.</div>';
    }
    $stmt_user->close();

    // Update password if provided
    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_pass = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt_pass->bind_param("si", $hashed_password, $admin_id);
            if($stmt_pass->execute()){
                 $message .= '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Password updated successfully.</div>';
            } else {
                $message .= '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error updating password.</div>';
            }
            $stmt_pass->close();
        } else {
            $message .= '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">New passwords do not match.</div>';
        }
    }
}

// Fetch current admin data
$stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'common/header.php';
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">Admin Settings</h1>

    <?php echo $message; ?>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="setting.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block mb-2 text-sm font-medium">Admin Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" class="w-full bg-gray-700 rounded p-2" required>
            </div>
            <hr class="border-gray-700 my-6">
            <h2 class="text-lg font-semibold mb-4">Change Password</h2>
             <div class="mb-4">
                <label for="new_password" class="block mb-2 text-sm font-medium">New Password (leave blank to keep current)</label>
                <input type="password" id="new_password" name="new_password" class="w-full bg-gray-700 rounded p-2">
            </div>
             <div class="mb-4">
                <label for="confirm_password" class="block mb-2 text-sm font-medium">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full bg-gray-700 rounded p-2">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg">Update Settings</button>
        </form>
    </div>
</main>

<?php include 'common/bottom.php'; ?>