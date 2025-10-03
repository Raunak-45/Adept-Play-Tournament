<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'common/config.php';
$user_id = $_SESSION['user_id'];
$message = '';

// Handle joining a tournament
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join_tournament'])) {
    $tournament_id = $_POST['tournament_id'];

    // Get tournament details and user balance in a single transaction
    $conn->begin_transaction();
    try {
        // Get tournament entry fee
        $stmt_fee = $conn->prepare("SELECT entry_fee FROM tournaments WHERE id = ?");
        $stmt_fee->bind_param("i", $tournament_id);
        $stmt_fee->execute();
        $result_fee = $stmt_fee->get_result();
        if($result_fee->num_rows === 0) throw new Exception("Tournament not found.");
        $tournament = $result_fee->fetch_assoc();
        $entry_fee = $tournament['entry_fee'];
        $stmt_fee->close();
        
        // Get user wallet balance and lock the row for update
        $stmt_balance = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
        $stmt_balance->bind_param("i", $user_id);
        $stmt_balance->execute();
        $result_balance = $stmt_balance->get_result();
        $user = $result_balance->fetch_assoc();
        $wallet_balance = $user['wallet_balance'];
        $stmt_balance->close();

        // Check if user has sufficient balance
        if ($wallet_balance >= $entry_fee) {
            // Deduct entry fee
            $new_balance = $wallet_balance - $entry_fee;
            $stmt_deduct = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt_deduct->bind_param("di", $new_balance, $user_id);
            $stmt_deduct->execute();
            $stmt_deduct->close();
            
            // Add to participants
            $stmt_join = $conn->prepare("INSERT INTO participants (user_id, tournament_id) VALUES (?, ?)");
            $stmt_join->bind_param("ii", $user_id, $tournament_id);
            $stmt_join->execute();
            $stmt_join->close();

            // Record transaction
            $description = "Joined Tournament #" . $tournament_id;
            $stmt_trans = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
            $stmt_trans->bind_param("ids", $user_id, $entry_fee, $description);
            $stmt_trans->execute();
            $stmt_trans->close();

            $conn->commit();
            $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Successfully joined the tournament!</div>';
        } else {
            throw new Exception("Insufficient balance.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        if ($e->getCode() == 1062) { // Duplicate entry
            $message = '<div class="bg-yellow-500 text-black p-3 rounded-lg mb-4 text-center">You have already joined this tournament.</div>';
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

include 'common/header.php';

// Fetch upcoming tournaments the user has NOT joined
$stmt = $conn->prepare("
    SELECT t.* 
    FROM tournaments t
    LEFT JOIN participants p ON t.id = p.tournament_id AND p.user_id = ?
    WHERE t.status = 'Upcoming' AND p.user_id IS NULL
    ORDER BY t.match_time ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tournaments = $stmt->get_result();
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6">Upcoming Tournaments</h1>

    <?php echo $message; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php if ($tournaments->num_rows > 0): ?>
            <?php while($row = $tournaments->fetch_assoc()): ?>
            <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h2 class="text-xl font-bold text-indigo-400"><?= htmlspecialchars($row['title']) ?></h2>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($row['game_name']) ?></p>
                    </div>
                    <div class="text-right">
                         <p class="text-xs text-gray-400">Prize Pool</p>
                        <p class="font-bold text-lg text-green-400">₹<?= number_format($row['prize_pool']) ?></p>
                    </div>
                </div>

                <div class="border-t border-b border-gray-700 my-3 py-2 text-sm text-gray-300 grid grid-cols-2 gap-2">
                    <div>
                        <p class="font-semibold">Match Time:</p>
                        <p><?= date('M j, Y g:i A', strtotime($row['match_time'])) ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Entry Fee:</p>
                        <p class="text-yellow-400">₹<?= number_format($row['entry_fee']) ?></p>
                    </div>
                </div>
                
                <form action="index.php" method="POST">
                    <input type="hidden" name="tournament_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="join_tournament" class="w-full mt-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Join Now
                    </button>
                </form>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-1 md:col-span-2 text-center text-gray-400 py-10">
                <i class="fas fa-trophy fa-3x mb-4"></i>
                <p>No upcoming tournaments available to join.</p>
                <p class="text-sm">Check back later or view the ones you've joined in "My Tournaments".</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'common/bottom.php'; ?>