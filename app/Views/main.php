<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - PetPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
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

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="top-bar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle d-lg-none" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 ms-3 ms-lg-0">Dashboard</h4>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted">Welcome back!</span>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger flash-message">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success flash-message">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <h2>Welcome to PetPal</h2>
                <p>Manage your pet-related items and information with ease. Use the navigation menu to access all
                    features.</p>
            </div>

            <!-- Quick Stats -->
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon bg-primary mx-auto">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h5>Add Items</h5>
                        <p class="text-muted mb-0">Add new pet items to your collection</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon bg-success mx-auto">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5>Profile</h5>
                        <p class="text-muted mb-0">Manage your account settings</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon bg-warning mx-auto">
                            <i class="fas fa-trash"></i>
                        </div>
                        <h5>Delete Items</h5>
                        <p class="text-muted mb-0">Remove items from your collection</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon bg-info mx-auto">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5>Coming Soon</h5>
                        <p class="text-muted mb-0">More features under development</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar Toggle Functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Close sidebar when clicking on a link (mobile)
        const navLinks = document.querySelectorAll('.sidebar .nav-link:not(.maintenance)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                closeSidebar();
            }
        });

        // Flash message auto-hide
        setTimeout(function () {
            const alerts = document.querySelectorAll('.flash-message');
            alerts.forEach(function (el) {
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-20px)';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);

        // Add active state to navigation
        const currentPath = window.location.pathname;
        const navLinksAll = document.querySelectorAll('.sidebar .nav-link');

        navLinksAll.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                document.querySelector('.nav-link.active').classList.remove('active');
                link.classList.add('active');
            }
        });

        document.getElementById('sidebarMinimizeBtn').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            var mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('minimized');
            mainContent.classList.toggle('sidebar-minimized');
        });
    </script>
</body>

</html>