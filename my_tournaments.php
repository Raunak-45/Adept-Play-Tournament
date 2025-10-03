<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'common/config.php';
$user_id = $_SESSION['user_id'];
include 'common/header.php';

// Fetch Upcoming/Live tournaments for the user
$stmt_upcoming = $conn->prepare("
    SELECT t.* FROM tournaments t
    JOIN participants p ON t.id = p.tournament_id
    WHERE p.user_id = ? AND t.status IN ('Upcoming', 'Live')
    ORDER BY t.match_time ASC
");
$stmt_upcoming->bind_param("i", $user_id);
$stmt_upcoming->execute();
$upcoming_tournaments = $stmt_upcoming->get_result();

// Fetch Completed tournaments for the user
$stmt_completed = $conn->prepare("
    SELECT t.* FROM tournaments t
    JOIN participants p ON t.id = p.tournament_id
    WHERE p.user_id = ? AND t.status = 'Completed'
    ORDER BY t.match_time DESC
");
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$completed_tournaments = $stmt_completed->get_result();
?>

<main class="flex-grow p-4" x-data="{ tab: 'upcoming' }">
    <h1 class="text-2xl font-bold mb-4">My Tournaments</h1>

    <div class="bg-gray-800 rounded-lg p-1.5 mb-6">
        <div class="flex border-b border-gray-700">
            <button @click="tab = 'upcoming'" :class="{'bg-indigo-600': tab === 'upcoming'}" class="flex-1 py-3 text-center font-semibold rounded-t-lg transition-colors">Upcoming/Live</button>
            <button @click="tab = 'completed'" :class="{'bg-indigo-600': tab === 'completed'}" class="flex-1 py-3 text-center font-semibold rounded-t-lg transition-colors">Completed</button>
        </div>
    </div>

    <!-- Upcoming/Live Tab -->
    <div x-show="tab === 'upcoming'">
        <div class="grid grid-cols-1 gap-4">
            <?php if ($upcoming_tournaments->num_rows > 0): ?>
                <?php while($row = $upcoming_tournaments->fetch_assoc()): ?>
                <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h2 class="text-xl font-bold text-indigo-400"><?= htmlspecialchars($row['title']) ?></h2>
                            <p class="text-sm text-gray-400"><?= htmlspecialchars($row['game_name']) ?></p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $row['status'] == 'Live' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-black' ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </div>
                     <div class="border-t border-gray-700 my-3 py-2 text-sm text-gray-300">
                        <p><span class="font-semibold">Match Time:</span> <?= date('M j, Y g:i A', strtotime($row['match_time'])) ?></p>
                         <p><span class="font-semibold">Prize Pool:</span> <span class="text-green-400">₹<?= number_format($row['prize_pool']) ?></span></p>
                    </div>

                    <?php if ($row['status'] == 'Live' && !empty($row['room_id'])): ?>
                    <div class="bg-gray-700 rounded-lg p-3 mt-3">
                        <h3 class="font-semibold mb-2 text-center text-indigo-300">Room Details</h3>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <p class="text-xs text-gray-400">Room ID</p>
                                <p class="font-mono bg-gray-900 p-2 rounded"><?= htmlspecialchars($row['room_id']) ?></p>
                            </div>
                             <div>
                                <p class="text-xs text-gray-400">Password</p>
                                <p class="font-mono bg-gray-900 p-2 rounded"><?= htmlspecialchars($row['room_password']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-gray-400 py-10">
                    <i class="far fa-calendar-alt fa-3x mb-4"></i>
                    <p>You haven't joined any upcoming tournaments.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Completed Tab -->
    <div x-show="tab === 'completed'" style="display: none;">
        <div class="grid grid-cols-1 gap-4">
             <?php if ($completed_tournaments->num_rows > 0): ?>
                <?php while($row = $completed_tournaments->fetch_assoc()): ?>
                <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <h2 class="text-lg font-bold text-indigo-400"><?= htmlspecialchars($row['title']) ?></h2>
                            <p class="text-xs text-gray-400"><?= date('M j, Y', strtotime($row['match_time'])) ?></p>
                        </div>
                        <div>
                            <?php
                            $result_text = "Participated";
                            $result_class = "bg-gray-600 text-white";
                            if ($row['winner_id'] == $user_id) {
                                $result_text = "Winner";
                                $result_class = "bg-green-500 text-white";
                            }
                            ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $result_class ?>">
                                <?= $result_text ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                 <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-history fa-3x mb-4"></i>
                    <p>No completed tournaments found in your history.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php include 'common/bottom.php'; ?>