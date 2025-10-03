<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin - Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen overflow-hidden select-none">
    <header class="bg-gray-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="text-xl font-bold">Admin Panel</h1>
        <a href="logout.php" class="text-gray-300 hover:text-white transition-colors">
            <i class="fas fa-sign-out-alt fa-lg"></i>
        </a>
    </header>```

#### 📄 bottom.php
```php
<?php
if(isset($conn)) {
    $conn->close();
}
?>
    <footer class="bg-gray-800 shadow-t-md p-2 flex justify-around items-center sticky bottom-0 z-10 mt-auto">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $nav_items = [
            ['page' => 'index.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
            ['page' => 'tournament.php', 'icon' => 'fas fa-trophy', 'label' => 'Tournaments'],
            ['page' => 'user.php', 'icon' => 'fas fa-users', 'label' => 'Users'],
            ['page' => 'setting.php', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
        ];

        foreach ($nav_items as $item) {
            $is_active = ($current_page == $item['page']);
            $text_color = $is_active ? 'text-indigo-400' : 'text-gray-400';
            echo "<a href='{$item['page']}' class='flex flex-col items-center w-1/4 {$text_color} hover:text-indigo-300 transition-colors'>";
            echo "<i class='{$item['icon']} fa-lg'></i>";
            echo "<span class='text-xs mt-1'>{$item['label']}</span>";
            echo "</a>";
        }
        ?>
    </footer>
    
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('wheel', e => {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=')) {
                e.preventDefault();
            }
        }, { passive: false });
    </script>
</body>
</html>