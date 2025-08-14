<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPal - Inventory</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= base_url('/assets/css/style.css') ?>" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
    </style>

    <script>
        $(document).ready(function () {
            const table = $('#itemTable').DataTable();
        });
    </script>
</head>

<body>
    <?= $this->include('partials/sidebar') ?>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">

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
                <h2>Inventory</h2>
                <p>View and manage user and pet inventory below.</p>
            </div>

            <!-- Inventory Search -->
            <form method="get" class="mb-4">
                <div class="d-flex justify-content-end">
                    <input type="text" name="search" class="form-control form-control-sm w-auto me-2" placeholder="Search items..." value="<?= esc($_GET['search'] ?? '') ?>">
                    <button class="btn btn-orange btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <?php
                // Pagination settings
                $itemsPerPage = 12;
                $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

                // Filter inventory by search term
                $filteredInventory = [];
                if (!empty($inventory) && is_array($inventory)) {
                    if ($searchTerm !== '') {
                        foreach ($inventory as $item) {
                            if (
                                stripos($item['item_name'] ?? '', $searchTerm) !== false ||
                                stripos($item['rarity'] ?? '', $searchTerm) !== false
                            ) {
                                $filteredInventory[] = $item;
                            }
                        }
                    } else {
                        $filteredInventory = $inventory;
                    }
                }

                $totalItems = count($filteredInventory);
                $totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
                $startIndex = ($currentPage - 1) * $itemsPerPage;
                $pagedInventory = $totalItems > 0 ? array_slice($filteredInventory, $startIndex, $itemsPerPage) : [];
            ?>

            <div class="row g-4">
                <?php if (!empty($pagedInventory) && is_array($pagedInventory)): ?>
                    <?php foreach ($pagedInventory as $item): ?>
                        <div class="col-md-2">
                            <div class="card h-100">
                                <img src="<?= esc($item['image_url'] ?? base_url('assets/images/default_item_photo.png')) ?>"
                                     class="card-img-top img-fluid"
                                     alt="<?= esc($item['item_name'] ?? '') ?>"
                                     style="height: 80px; width: auto; object-fit: contain; margin: 0 auto; display: block;">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?= esc($item['item_name'] ?? 'Unknown') ?></h5>
                                    <?php
                                        $rarity = $item['rarity'] ?? 'Unknown';
                                        $bgColor = 'FFFFFF'; // default common

                                        switch (strtolower($rarity)) {
                                            case 'godlike':
                                                $bgColor = 'FFD700';
                                                $textColor = '000';
                                                break;
                                            case 'legendary':
                                                $bgColor = 'FF8000';
                                                $textColor = '000';
                                                break;
                                            case 'mythic':
                                                $bgColor = 'FF5C5C';
                                                $textColor = '000';
                                                break;
                                            case 'epic':
                                                $bgColor = 'A335EE';
                                                $textColor = 'fff';
                                                break;
                                            case 'ultra rare':
                                                $bgColor = '80FFFF';
                                                $textColor = '000';
                                                break;
                                            case 'uncommon':
                                                $bgColor = '1EFF00';
                                                $textColor = '000';
                                                break;
                                            case 'rare':
                                                $bgColor = '0070DD';
                                                $textColor = 'fff';
                                                break;
                                            case 'common':
                                            default:
                                                $bgColor = 'FFFFFF';
                                                $textColor = '000';
                                                break;
                                        }
                                    ?>
                                    <p class="card-text" style="background-color: #<?= $bgColor ?>; color: #<?= $textColor ?>; border-radius: 5px; padding: 4px 4px;">
                                        <?= esc($rarity); ?>
                                    </p>
                                    <p class="card-text">Quantity: <?= esc($item['quantity'] ?? 0) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No inventory items found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Inventory pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                            <li class="page-item <?= ($page == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="?search=<?= urlencode($searchTerm) ?>&page=<?= $page ?>"><?= $page ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>


        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
<!-- JS includes -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>

</html>