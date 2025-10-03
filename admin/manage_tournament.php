<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../common/config.php';
$message = '';

if (!isset($_GET['id'])) {
    header("Location: tournament.php");
    exit();
}
$tournament_id = $_GET['id'];

// Handle Room Details Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];
    $room_password = $_POST['room_password'];
    
    $stmt = $conn->prepare("UPDATE tournaments SET room_id=?, room_password=?, status='Live' WHERE id=?");
    $stmt->bind_param("ssi", $room_id, $room_password, $tournament_id);
    if($stmt->execute()){
        $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Room details updated and tournament is now Live.</div>';
    } else {
        $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error updating room details.</div>';
    }
    $stmt->close();
}

// Handle Winner Declaration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['declare_winner'])) {
    $winner_id = $_POST['winner_id'];
    
    $conn->begin_transaction();
    try {
        // Get prize pool
        $stmt_prize = $conn->prepare("SELECT prize_pool FROM tournaments WHERE id = ?");
        $stmt_prize->bind_param("i", $tournament_id);
        $stmt_prize->execute();
        $prize_pool = $stmt_prize->get_result()->fetch_assoc()['prize_pool'];
        $stmt_prize->close();
        
        // Add prize to winner's wallet
        $stmt_credit = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt_credit->bind_param("di", $prize_pool, $winner_id);
        $stmt_credit->execute();
        $stmt_credit->close();
        
        // Update tournament status and winner
        $stmt_complete = $conn->prepare("UPDATE tournaments SET status = 'Completed', winner_id = ? WHERE id = ?");
        $stmt_complete->bind_param("ii", $winner_id, $tournament_id);
        $stmt_complete->execute();
        $stmt_complete->close();

        // Record transaction for winner
        $description = "Won Tournament #" . $tournament_id;
        $stmt_trans = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
        $stmt_trans->bind_param("ids", $winner_id, $prize_pool, $description);
        $stmt_trans->execute();
        $stmt_trans->close();

        $conn->commit();
        $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Winner declared and prize distributed successfully!</div>';
    } catch (Exception $e) {
        $conn->rollback();
        $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error: ' . $e->getMessage() . '</div>';
    }
}


// Fetch tournament details
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch participants
$stmt_participants = $conn->prepare("SELECT u.id, u.username FROM users u JOIN participants p ON u.id = p.user_id WHERE p.tournament_id = ?");
$stmt_participants->bind_param("i", $tournament_id);
$stmt_participants->execute();
$participants = $stmt_participants->get_result();
$stmt_participants->close();

include 'common/header.php';
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-2">Manage Tournament</h1>
    <p class="text-indigo-400 mb-6"><?= htmlspecialchars($tournament['title']) ?></p>

    <?php echo $message; ?>

    <!-- Update Room Details -->
    <?php if ($tournament['status'] != 'Completed'): ?>
    <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Room ID & Password</h2>
        <form action="manage_tournament.php?id=<?= $tournament_id ?>" method="POST">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="room_id" class="block mb-2 text-sm font-medium">Room ID</label>
                    <input type="text" name="room_id" value="<?= htmlspecialchars($tournament['room_id']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
                <div>
                     <label for="room_password" class="block mb-2 text-sm font-medium">Room Password</label>
                    <input type="text" name="room_password" value="<?= htmlspecialchars($tournament['room_password']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
            </div>
             <button type="submit" name="update_room" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg">Update & Set Live</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Declare Winner -->
    <?php if ($tournament['status'] != 'Completed'): ?>
    <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Declare Winner</h2>
        <form action="manage_tournament.php?id=<?= $tournament_id ?>" method="POST">
            <div class="mb-4">
                 <label for="winner_id" class="block mb-2 text-sm font-medium">Select Winner</label>
                 <select name="winner_id" id="winner_id" class="w-full bg-gray-700 rounded p-3" required>
                    <option value="" disabled selected>-- Select a participant --</option>
                    <?php 
                    mysqli_data_seek($participants, 0); // Reset pointer
                    while($p = $participants->fetch_assoc()): 
                    ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['username']) ?></option>
                    <?php endwhile; ?>
                 </select>
            </div>
            <button type="submit" name="declare_winner" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg">Declare Winner & Distribute Prize</button>
        </form>
    </div>
    <?php else: ?>
        <div class="bg-gray-800 rounded-lg p-6 mb-8 text-center">
            <h2 class="text-2xl font-bold text-green-400"><i class="fas fa-check-circle"></i> Tournament Completed</h2>
            <?php
                if($tournament['winner_id']){
                    $winner_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                    $winner_stmt->bind_param("i", $tournament['winner_id']);
                    $winner_stmt->execute();
                    $winner_name = $winner_stmt->get_result()->fetch_assoc()['username'];
                    echo "<p class='mt-2'>Winner: <strong class='text-yellow-400'>".htmlspecialchars($winner_name)."</strong></p>";
                }
            ?>
        </div>
    <?php endif; ?>


    <!-- Participants List -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Participants (<?= $participants->num_rows ?>)</h2>
        <div class="space-y-2">
        <?php
        mysqli_data_seek($participants, 0); // Reset pointer
        if ($participants->num_rows > 0) {
            while($p = $participants->fetch_assoc()) {
                echo "<div class='bg-gray-700 p-3 rounded-md'>" . htmlspecialchars($p['username']) . "</div>";
            }
        } else {
            echo "<p class='text-gray-400'>No participants yet.</p>";
        }
        ?>
        </div>
    </div>
</main>

<?php include 'common/bottom.php'; ?>