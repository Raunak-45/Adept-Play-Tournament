<?php
// This check ensures config is only required once if already included.
if (!isset($conn)) {
    require_once 'config.php';
}

$wallet_balance = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $wallet_balance = $result->fetch_assoc()['wallet_balance'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
            -webkit-tap-highlight-color: transparent; /* Remove tap highlight */
        }
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #1a202c; /* bg-gray-800 */
        }
        ::-webkit-scrollbar-thumb {
            background: #4a5568; /* bg-gray-600 */
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen overflow-hidden select-none">
    <header class="bg-gray-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="text-xl font-bold">Adept Play</h1>
        <a href="wallet.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
            <i class="fas fa-wallet"></i>
            <span>₹<?= number_format($wallet_balance, 2) ?></span>
        </a>
    </header>