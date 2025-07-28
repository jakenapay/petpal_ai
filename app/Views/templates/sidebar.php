<div class="sidebar" id="sidebar">
    <!-- <h3><i class="fas fa-paw me-2"></i>PetPal</h3> -->
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
            <a href="#" class="nav-link active">
                <i class="fas fa-home"></i>
                <span class="px-2">Dashboard</span>
            </a>
            <a href="<?= base_url('profile'); ?>" class="nav-link">
                <i class="fas fa-user"></i>
                <span class="px-2">Profile</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Item Management</div>
            <a href="<?= base_url('item/list'); ?>" class="nav-link">
                <i class="fas fa-box"></i>
                <span class="px-2">Items</span>
            </a>
            <!-- <a href="<?= base_url('item/delete'); ?>" class="nav-link">
                    <i class="fas fa-trash-alt"></i>
                    <span class="px-2">Delete Item</span>
                </a> -->
        </div>

        <!-- <div class="nav-section">
                <div class="nav-section-title">Under Maintenance</div>
                <a href="#" class="nav-link maintenance" title="Coming Soon">
                    <i class="fas fa-edit"></i>
                    <span class="px-2">Update Item</span>
                </a>
                <a href="#" class="nav-link maintenance" title="Coming Soon">
                    <i class="fas fa-search"></i>
                    <span class="px-2">Get Item</span>
                </a>
            </div> -->

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