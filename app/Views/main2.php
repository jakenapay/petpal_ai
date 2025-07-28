<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'PetPal') ?></title>
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
    <!-- Sidebar (reusable partial) -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="top-bar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle d-lg-none" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 ms-3 ms-lg-0"><?= esc($pageTitle ?? 'Dashboard') ?></h4>
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

            <!-- Main dynamic page content -->
            <?= $this->renderSection('content') ?>
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

        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

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

        const minimizeBtn = document.getElementById('sidebarMinimizeBtn');
        if (minimizeBtn) {
            minimizeBtn.addEventListener('click', function () {
                var sidebar = document.getElementById('sidebar');
                var mainContent = document.querySelector('.main-content');
                sidebar.classList.toggle('minimized');
                mainContent.classList.toggle('sidebar-minimized');
            });
        }
    </script>
</body>
</html>
