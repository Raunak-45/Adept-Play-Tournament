<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../common/config.php';
$message = '';
$edit_mode = false;
$tournament_data = ['id' => '', 'title' => '', 'game_name' => '', 'entry_fee' => '', 'prize_pool' => '', 'match_time' => '', 'commission_percentage' => '20'];

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add or Update
    if (isset($_POST['save_tournament'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $game_name = $_POST['game_name'];
        $entry_fee = $_POST['entry_fee'];
        $prize_pool = $_POST['prize_pool'];
        $match_time = $_POST['match_time'];
        $commission_percentage = $_POST['commission_percentage'];
        
        if ($id) { // Update
            $stmt = $conn->prepare("UPDATE tournaments SET title=?, game_name=?, entry_fee=?, prize_pool=?, match_time=?, commission_percentage=? WHERE id=?");
            $stmt->bind_param("ssddssi", $title, $game_name, $entry_fee, $prize_pool, $match_time, $commission_percentage, $id);
            $action = 'updated';
        } else { // Insert
            $stmt = $conn->prepare("INSERT INTO tournaments (title, game_name, entry_fee, prize_pool, match_time, commission_percentage) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddss", $title, $game_name, $entry_fee, $prize_pool, $match_time, $commission_percentage);
            $action = 'created';
        }

        if ($stmt->execute()) {
            $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Tournament ' . $action . ' successfully.</div>';
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
    
    // Delete
    if (isset($_POST['delete_tournament'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = '<div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">Tournament deleted successfully.</div>';
        } else {
            $message = '<div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">Error deleting tournament.</div>';
        }
        $stmt->close();
    }
}

// Handle Edit request
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tournament_data = $result->fetch_assoc();
    $tournament_data['match_time'] = date('Y-m-d\TH:i', strtotime($tournament_data['match_time']));
    $stmt->close();
}

include 'common/header.php';

// Fetch all tournaments
$tournaments = $conn->query("SELECT * FROM tournaments ORDER BY created_at DESC");
?>

<main class="flex-grow p-4">
    <h1 class="text-2xl font-bold mb-6"><?= $edit_mode ? 'Edit' : 'Add New' ?> Tournament</h1>

    <?php echo $message; ?>

    <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <form action="tournament.php" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($tournament_data['id']) ?>">
            <div class="mb-4">
                <label for="title" class="block mb-2 text-sm font-medium">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($tournament_data['title']) ?>" class="w-full bg-gray-700 rounded p-2" required>
            </div>
            <div class="mb-4">
                <label for="game_name" class="block mb-2 text-sm font-medium">Game Name</label>
                <input type="text" id="game_name" name="game_name" value="<?= htmlspecialchars($tournament_data['game_name']) ?>" class="w-full bg-gray-700 rounded p-2" required>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="entry_fee" class="block mb-2 text-sm font-medium">Entry Fee (₹)</label>
                    <input type="number" step="0.01" id="entry_fee" name="entry_fee" value="<?= htmlspecialchars($tournament_data['entry_fee']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
                 <div>
                    <label for="prize_pool" class="block mb-2 text-sm font-medium">Prize Pool (₹)</label>
                    <input type="number" step="0.01" id="prize_pool" name="prize_pool" value="<?= htmlspecialchars($tournament_data['prize_pool']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                     <label for="match_time" class="block mb-2 text-sm font-medium">Match Time</label>
                    <input type="datetime-local" id="match_time" name="match_time" value="<?= htmlspecialchars($tournament_data['match_time']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
                 <div>
                    <label for="commission_percentage" class="block mb-2 text-sm font-medium">Commission (%)</label>
                    <input type="number" id="commission_percentage" name="commission_percentage" value="<?= htmlspecialchars($tournament_data['commission_percentage']) ?>" class="w-full bg-gray-700 rounded p-2" required>
                </div>
            </div>
            <button type="submit" name="save_tournament" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg">
                <?= $edit_mode ? 'Update' : 'Create' ?> Tournament
            </button>
             <?php if ($edit_mode): ?>
                <a href="tournament.php" class="block text-center w-full mt-2 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 rounded-lg">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <h2 class="text-xl font-semibold mb-4">All Tournaments</h2>
    <div class="space-y-3">
    <?php while($row = $tournaments->fetch_assoc()): ?>
        <div class="bg-gray-800 rounded-lg p-3 flex items-center justify-between">
            <div>
                <p class="font-bold text-indigo-400"><?= htmlspecialchars($row['title']) ?></p>
                <p class="text-sm text-gray-400"><?= htmlspecialchars($row['game_name']) ?> - <span class="text-yellow-400">₹<?= $row['entry_fee'] ?></span></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="manage_tournament.php?id=<?= $row['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm">Manage</a>
                 <a href="tournament.php?edit=<?= $row['id'] ?>" class="bg-yellow-500 text-black px-3 py-1 rounded-md text-sm">Edit</a>
                 <form action="tournament.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this tournament?');" class="inline">
                     <input type="hidden" name="id" value="<?= $row['id'] ?>">
                     <button type="submit" name="delete_tournament" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm">Delete</button>
                 </form>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</main>

<?php include 'common/bottom.php'; ?>