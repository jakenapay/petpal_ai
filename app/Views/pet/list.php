<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Users List</title>
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
            <h2 class="mb-4">Pets List</h2>
            <div class="col-md-12">

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

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert flash-message alert-danger text-center">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
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
                    <th>Pet ID</th>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Specie</th>
                    <th>Breed</th>
                    <th>Level</th>
                    <th>Life Stage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pets)): ?>
                    <?php foreach ($pets as $pet): ?>
                        <tr>
                            <td><?= esc($pet['pet_id']) ?></td>
                            <td class="text-capitalize"><?= esc($pet['name']) ?></td>
                            <td class="text-capitalize"><?= esc($pet['owner_name'] ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($pet['species'] ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($pet['breed'] ?? '-') ?></td>
                            <td class="text-center"><?= esc($pet['level'] ?? '-') ?></td>
                            <td><?= esc($pet['life_stage'] ?? '-') ?></td>
                            <?php
                            $statusColor = 'secondary';
                            if ($pet['status'] === 'active') {
                                $statusColor = 'success';
                            } elseif ($pet['status'] === 'inactive') {
                                $statusColor = 'danger';
                            } elseif ($pet['status'] === 'suspended') {
                                $statusColor = 'warning';
                            }
                            ?>
                            <td>
                            <span class="text-capitalize badge bg-<?= $statusColor ?>"><?= esc($pet['status']) ?></span>
                            </td>
                            <td>
                                <!-- Delete Button: Uses POST -->
                                <form action="<?= base_url('pets/delete/' . esc($pet['pet_id'])) ?>" method="post"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn m-0 btn-sm btn-danger" data-bs-toggle="tooltip"
                                        title="Delete Pet"
                                        disabled
                                        onclick="return confirm('Are you sure you want to delete this pet?');">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                <!-- Edit Button: Uses GET -->
                                <a href="<?= base_url('pets/edit/' . esc($pet['pet_id'])) ?>" class="btn btn-sm btn-orange"
                                    data-bs-toggle="tooltip" title="Edit Pet">
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