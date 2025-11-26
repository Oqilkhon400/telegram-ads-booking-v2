<?php
// Current page detection
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="fixed left-0 top-16 h-screen w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40" id="sidebar">
    <div class="p-6 h-full overflow-y-auto">
        
        <!-- Navigation Menu -->
        <nav class="space-y-2">
            
            <!-- Dashboard -->
            <a href="index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-100 transition <?php echo $current_page == 'dashboard.php' ? 'bg-blue-100 text-blue-600' : 'text-gray-700'; ?>">
                <i class="fas fa-home text-xl"></i>
                <span class="font-medium">Bosh Sahifa</span>
            </a>
            
            <!-- Calendar -->
            <a href="calendar.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-100 transition <?php echo $current_page == 'calendar.php' ? 'bg-green-100 text-green-600' : 'text-gray-700'; ?>">
                <i class="fas fa-calendar-alt text-xl"></i>
                <span class="font-medium">Kalendar</span>
            </a>
            
            <!-- Customers -->
            <a href="customers.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-purple-100 transition <?php echo $current_page == 'customers.php' ? 'bg-purple-100 text-purple-600' : 'text-gray-700'; ?>">
                <i class="fas fa-users text-xl"></i>
                <span class="font-medium">Mijozlar</span>
            </a>
            
            <!-- Packages -->
            <a href="packages.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition <?php echo $current_page == 'packages.php' ? 'bg-orange-100 text-orange-600' : 'text-gray-700'; ?>">
                <i class="fas fa-box text-xl"></i>
                <span class="font-medium">Paketlar</span>
            </a>
            
            <!-- Payments -->
            <a href="payments.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-yellow-100 transition <?php echo $current_page == 'payments.php' ? 'bg-yellow-100 text-yellow-600' : 'text-gray-700'; ?>">
                <i class="fas fa-money-bill-wave text-xl"></i>
                <span class="font-medium">To'lovlar</span>
            </a>
            
            
            <!-- Statistics -->
            <a href="statistics.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-pink-100 transition <?php echo $current_page == 'statistics.php' ? 'bg-pink-100 text-pink-600' : 'text-gray-700'; ?>">
                <i class="fas fa-chart-line text-xl"></i>
                <span class="font-medium">Statistika</span>
            </a>
            
            <hr class="my-4 border-gray-200">
            
            <!-- Settings - YANGI NOM! -->
            <a href="app-config.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-cyan-100 transition <?php echo $current_page == 'app-config.php' ? 'bg-cyan-100 text-cyan-600' : 'text-gray-700'; ?>">
                <i class="fas fa-cog text-xl"></i>
                <span class="font-medium">Sozlamalar</span>
            </a>

             <!-- Yordam -->
        <a href="help.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $current_page === 'help' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
            <i class="fas fa-question-circle text-lg w-5"></i>
            <span class="font-medium">Yordam</span>
        </a>
            
            <!-- Logout -->
            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-100 text-red-600 transition">
                <i class="fas fa-sign-out-alt text-xl"></i>
                <span class="font-medium">Chiqish</span>
            </a>
            
        </nav>
        
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>