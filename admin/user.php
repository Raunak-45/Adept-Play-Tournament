<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../common/config.php';
include 'common/header.php';

// Fetch all users
$users = $conn->query("SELECT id, username, email, wallet_balance, created_at FROM users ORDER BY created_at DESC");
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">Manage Users</h1>
    
    <div class="bg-gray-800 rounded-lg shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-700 text-xs uppercase">
                    <tr>
                        <th scope="col" class="px-4 py-3">Username</th>
                        <th scope="col" class="px-4 py-3">Email</th>
                        <th scope="col" class="px-4 py-3">Balance</th>
                        <th scope="col" class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($row = $users->fetch_assoc()): ?>
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="px-4 py-3 text-green-400">₹<?= number_format($row['wallet_balance'], 2) ?></td>
                            <td class="px-4 py-3">
                                <!-- Dummy buttons for now -->
                                <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">Block</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-400">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include 'common/bottom.php'; ?>