<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - PetPal</title>
     <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body>
 <?= $this->include('partials/sidebar') ?>

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

 
</body>

</html>