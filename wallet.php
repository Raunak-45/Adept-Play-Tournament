<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'common/config.php';
$user_id = $_SESSION['user_id'];
include 'common/header.php';

// Fetch wallet balance
$stmt_balance = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$balance_result = $stmt_balance->get_result();
$user_balance = $balance_result->fetch_assoc()['wallet_balance'];
$stmt_balance->close();

// Fetch transaction history
$stmt_trans = $conn->prepare("SELECT amount, type, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt_trans->bind_param("i", $user_id);
$stmt_trans->execute();
$transactions = $stmt_trans->get_result();
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">My Wallet</h1>

    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl p-6 text-center shadow-lg mb-8">
        <p class="text-lg text-indigo-200">Current Balance</p>
        <p class="text-5xl font-bold tracking-tight mt-2">₹<?= number_format($user_balance, 2) ?></p>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <button class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg flex items-center justify-center gap-2 transition-colors">
            <i class="fas fa-plus-circle"></i> Add Money
        </button>
        <button class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 rounded-lg flex items-center justify-center gap-2 transition-colors">
            <i class="fas fa-arrow-circle-down"></i> Withdraw
        </button>
    </div>

    <!-- Transaction History -->
    <div>
        <h2 class="text-xl font-semibold mb-4">Transaction History</h2>
        <div class="space-y-3">
            <?php if ($transactions->num_rows > 0): ?>
                <?php while($row = $transactions->fetch_assoc()): ?>
                <div class="bg-gray-800 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $row['type'] == 'credit' ? 'bg-green-500' : 'bg-red-500' ?>">
                            <i class="fas <?= $row['type'] == 'credit' ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                        </div>
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($row['description']) ?></p>
                            <p class="text-sm text-gray-400"><?= date('M j, Y, g:i A', strtotime($row['created_at'])) ?></p>
                        </div>
                    </div>
                    <p class="font-bold text-lg <?= $row['type'] == 'credit' ? 'text-green-400' : 'text-red-400' ?>">
                        <?= $row['type'] == 'credit' ? '+' : '-' ?>₹<?= number_format($row['amount'], 2) ?>
                    </p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-exchange-alt fa-3x mb-4"></i>
                    <p>No transactions yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'common/bottom.php'; ?>