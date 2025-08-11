<style>
       .sidebar.minimized #sidebarMinimizeBtn {
        position: absolute;
        left: 10%; 
        transform: translateY(-5%);
        z-index: 10;
        display: flex !important;
    }
 
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-paw me-2"></i>PetPal</h3>
        <button id="sidebarMinimizeBtn" class="btn btn-sm btn-light d-none d-lg-inline ms-2" type="button"
            title="Minimize Sidebar">
            <i class="fas fa-angle-double-left"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main Navigation</div>
            <a href="<?= base_url('main'); ?>" class="nav-link">
                <i class="fas fa-home"></i>
                <span class="px-2">Dashboard</span>
            </a>
            <a href="<?= base_url('profile'); ?>" class="nav-link">
                <i class="fas fa-user"></i>
                <span class="px-2">Profile</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="<?= base_url('item/list'); ?>" class="nav-link">
                <i class="fas fa-box"></i>
                <span class="px-2">Items</span>
            </a>
            <a href="<?= base_url('users/list'); ?>" class="nav-link">
                <i class="fas fa-group"></i>
                <span class="px-3">Users</span>
            </a>
        </div> 

        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="<?= base_url('register'); ?>" class="nav-link">
                <i class="fas fa-user-plus"></i>
                <span class="px-2">Register</span>
            </a>
            <a href="<?= base_url('logout'); ?>" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span class="px-2">Logout</span>
            </a>
        </div>
    </nav>
</div>

<!-- Sidebar overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
</div>


<script>
    document.getElementById('sidebarMinimizeBtn').addEventListener('click', function () {
        var sidebar = document.getElementById('sidebar');
        var mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('minimized');
        mainContent.classList.toggle('sidebar-minimized');
    });
    
</script>