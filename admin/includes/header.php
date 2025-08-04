<div class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="ms-3 ms-lg-0">Panel Admin Sambatan</h4>
    </div>
    
    <div class="header-right">
        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <div class="admin-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="ms-2"><?= $_SESSION['admin_username'] ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profil</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}
</script>
