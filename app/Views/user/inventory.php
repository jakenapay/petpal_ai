<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inventory</title>
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
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }

        .form-control {
            padding: 5px 15px !important;
            font-size: 14px;
        }

        .modal-body .form-control {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?= $this->include('partials/sidebar') ?>

    <div class="container mt-4">
        <div class="row">
            <h2 class="mb-4">Inventory</h2>
            <div class="col-md-12">
                <!-- Add New Item Button triggers modal -->
                <button class="btn btn-sm btn-orange" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fa fa-plus me-1"></i> Add item in inventory
                </button>
                <!-- <button class="disabled btn btn-sm btn-orange" data-bs-toggle="modal" data-bs-target="#uploadItemModal">
                    <i class="fa fa-upload me-1"></i> Upload Bulk
                </button> -->

                <!-- Add item in inventory Modal -->
                <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <form action="<?= base_url('user/item/add') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addItemModalLabel">Add New Item in Inventory</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row justify-content-center align-items-start g-4">
                                        <!-- Column 1 -->
                                        <div class="col-md-6">
                                            <label>Username</label>
                                            <input type="hidden" value="<?= $user['user_id'] ?>"
                                                class="form-control form-control-sm" required name="user_id" readonly>
                                            <input type="text" value="<?= $user['username'] ?>"
                                                class="form-control form-control-sm" required name="username" readonly>

                                            <label>Item</label>
                                            <select name="item_id" class="form-control form-control-sm">
                                                <option value="" disabled selected>Select item</option>
                                                <?php foreach ($allItems as $item): ?>
                                                    <option value="<?= esc($item['item_id']) ?>">
                                                        <?= esc($item['item_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Acquisitions</label>
                                            <select name="acquisition" class="form-control form-control-sm">
                                                <option value="" disabled selected>Select acquisition type</option>
                                                <?php foreach ($allAcquisitions as $acquisition): ?>
                                                    <option value="<?= esc($acquisition['acquisition_type_id']) ?>">
                                                        <?= esc($acquisition['type_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>


                                            <label>Quantity</label>
                                            <input type="number" min="0" name="quantity"
                                                class="form-control form-control-sm" placeholder="0">

                                            <label>Acquisition Date</label>
                                            <input type="date" name="acquisition_date"
                                                class="form-control form-control-sm" required>

                                            <label>Expiration Date</label>
                                            <input type="date" name="expiration_date"
                                                class="form-control form-control-sm">
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="mx-2 btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="mx-2 btn btn-orange">Add Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Place for custom search bar -->
        <div id="custom-search-bar-container" class="mb-3"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 m-2">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert flash-message alert-danger text-center"><?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert flash-message alert-success text-center"><?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <table id="itemTable" class="table table-sm table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item ID</th>
                    <th>Item Name (Code)</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Date Acquired</th>
                    <th>Expiration Date</th>
                    <th>Equipped Count</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inventory) && is_array($inventory)): ?>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?= esc($item['id']) ?></td>
                            <td><?= esc($item['item_id']) ?></td>
                            <td><?= esc($item['item_name'] ?? '-') ?></td>
                            <td><?= esc($item['quantity']) ?></td>
                            <td><?= esc($item['category_name'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?= esc($item['image_url']) ?>" alt="Item" width="40">
                                <?php endif; ?>
                            </td>
                            <td><?= date('Y-m-d', strtotime(esc($item['acquisition_date']))) ?></td>
                            <td><?= !empty($item['expiration_date']) ? date('Y-m-d', strtotime($item['expiration_date'])) : '-' ?>
                            </td>
                            <td class="text-center"><?= esc($item['equipped_count'] ?? '-') ?></td>
                            <td class="d-flex justify-content-center gap-1">
                                <!-- Delete Button: Uses POST -->
                                <form action="<?= base_url('user/item/delete/' . esc($item['id'])) ?>" method="post"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn m-0 btn-sm btn-danger" data-bs-toggle="tooltip"
                                        title="Delete item to inventory"
                                        onclick="return confirm('Are you sure you want to delete this item to the inventory?');">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                <!-- Edit Button: Uses GET -->
                                <a href="<?= base_url('user/item/edit/' . esc($item['id'])) ?>" class="btn btn-sm btn-orange"
                                    data-bs-toggle="tooltip" title="Edit Item">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- JS includes -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.getElementById('categorySelector').addEventListener('change', function () {
            const value = this.value;

            // List of all fields to disable/enable
            const fieldIds = ['subCategory', 'specie', 'breed', 'iconUrl', 'addressableUrl', 'rgbColor'];

            // Determine if fields should be disabled (change '1' as needed)
            const shouldDisable = (value !== '1');

            // Loop through and disable/enable each field
            fieldIds.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.disabled = shouldDisable;
                }
            });
        });

        // Optional: trigger once on load
        document.getElementById('categorySelector').dispatchEvent(new Event('change'));
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        // JavaScript to hide flash messages after 5 seconds 
        setTimeout(function () {
            const alerts = document.querySelectorAll('.flash-message');
            alerts.forEach(function (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500); // remove element after fade out
            });
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
    <script>
        $(document).ready(function () {
            const table = $('#itemTable').DataTable();

            setTimeout(() => {
                const searchFilter = $('#itemTable_filter');
                const searchInput = searchFilter.find('input');
                if (searchInput.length) {
                    searchInput.removeClass('form-control'); // Remove Bootstrap class
                    searchInput.parent().removeClass('form-group'); // Remove wrapper class
                    searchInput.addClass('searchBar');
                    searchInput.attr('placeholder', 'Search items...');

                    $('#custom-search-bar-container').empty().append(searchInput.detach());
                    searchFilter.remove();
                }
            }, 0);
        });

    </script>
    <style>
        #custom-search-bar-container .searchBar {
            all: unset;
            /* Reset everything */
            display: inline-block;
            width: 260px;
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.4;
            color: #222;
            background-color: #f8f9fa;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
            transition: all 0.25s ease-in-out;
        }

        /* Focus effect */
        #custom-search-bar-container .searchBar:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 8px rgba(46, 125, 50, 0.5);
        }

        /* Force override Bootstrap */
        #custom-search-bar-container .searchBar.form-control,
        #custom-search-bar-container .searchBar.form-control:focus {
            all: unset !important;
            display: inline-block !important;
        }

        /* Table styling: Scoped to your specific ID to avoid Bootstrap overrides */
        #itemTable.dataTable {
            all: unset;
            /* Reset all Bootstrap effects */
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0 8px;
            /* gap between rows */
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #333;
        }

        #itemTable.dataTable thead tr th {
            background-color: #dd6e14;
            color: #fff;
            text-align: left;
            padding: 10px;
            border: none;
        }

        #itemTable.dataTable tbody tr {
            background-color: #ffffff;
            transition: background-color 0.2s;
        }

        #itemTable.dataTable tbody tr:hover {
            background-color: #f1f8f4;
        }

        #itemTable.dataTable tbody td {
            padding: 10px 12px;
            border: none;
        }

        /* Optional: Custom pagination and info styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background-color: #dd6e14 !important;
            color: #fff !important;
            border-radius: 4px;
            margin: 2px;
            border: none !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #dd6e14 !important;
        }
    </style>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            fileName.textContent = fileInput.files[0].name;
        });

        fileInput.addEventListener('change', () => {
            fileName.textContent = fileInput.files[0].name;
        });
    </script>

</body>

</html>