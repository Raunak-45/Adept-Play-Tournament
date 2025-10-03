<?php
$conn->close();
?>
    <footer class="bg-gray-800 shadow-t-md p-2 flex justify-around items-center sticky bottom-0 z-10 mt-auto">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $nav_items = [
            ['page' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Home'],
            ['page' => 'my_tournaments.php', 'icon' => 'fas fa-trophy', 'label' => 'My Tournaments'],
            ['page' => 'wallet.php', 'icon' => 'fas fa-wallet', 'label' => 'Wallet'],
            ['page' => 'profile.php', 'icon' => 'fas fa-user', 'label' => 'Profile'],
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

        // Disable zoom - more robust implementation
        document.addEventListener('wheel', e => {
            if (e.ctrlKey) {
                e.preventDefault();
            }
        }, { passive: false });

        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=')) {
                e.preventDefault();
            }
        }, { passive: false });

        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
          const now = (new Date()).getTime();
          if (now - lastTouchEnd <= 300) {
            event.preventDefault();
          }
          lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>