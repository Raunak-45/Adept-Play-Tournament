<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../common/config.php';
include 'common/header.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_tournaments = $conn->query("SELECT COUNT(*) as count FROM tournaments")->fetch_assoc()['count'];

$prize_result = $conn->query("SELECT SUM(prize_pool) as total FROM tournaments WHERE status = 'Completed'")->fetch_assoc();
$total_prize_distributed = $prize_result['total'] ?? 0;

$revenue_result = $conn->query("
    SELECT SUM(t.entry_fee * (t.commission_percentage / 100)) as total
    FROM tournaments t
    JOIN participants p ON t.id = p.tournament_id
    WHERE t.status = 'Completed'
")->fetch_assoc();
$total_revenue = $revenue_result['total'] ?? 0;

?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="bg-gray-800 p-4 rounded-lg text-center">
            <p class="text-3xl font-bold"><?= $total_users ?></p>
            <p class="text-sm text-gray-400">Total Users</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
            <p class="text-3xl font-bold"><?= $total_tournaments ?></p>
            <p class="text-sm text-gray-400">Total Tournaments</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
            <p class="text-3xl font-bold">₹<?= number_format($total_prize_distributed) ?></p>
            <p class="text-sm text-gray-400">Prize Distributed</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
            <p class="text-3xl font-bold">₹<?= number_format($total_revenue) ?></p>
            <p class="text-sm text-gray-400">Total Revenue</p>
        </div>
    </div>

    <a href="tournament.php" class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-lg transition-colors text-lg">
        <i class="fas fa-plus-circle mr-2"></i> Create New Tournament
    </a>

</main>

<?php include 'common/bottom.php'; ?>