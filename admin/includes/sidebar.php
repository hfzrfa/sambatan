<div class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/sambatanlogo.png" alt="Logo" style="width: 50px; height: 50px; margin-bottom: 10px;">
        <h3>Sambatan Admin</h3>
        <p class="mb-0">Coffee & Space</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                Pesanan
            </a>
        </li>
        <li>
            <a href="menu.php" class="<?= basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : '' ?>">
                <i class="fas fa-coffee"></i>
                Menu
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i>
                Kategori
            </a>
        </li>
        <li>
            <a href="customers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                Pelanggan
            </a>
        </li>
        <li>
            <a href="reviews.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
                <i class="fas fa-star"></i>
                Ulasan
            </a>
        </li>
        <li>
            <a href="gallery.php" class="<?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>">
                <i class="fas fa-images"></i>
                Galeri
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                Pengaturan
            </a>
        </li>
        <li>
            <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                Laporan
            </a>
        </li>
        <li>
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </li>
    </ul>
</div>
