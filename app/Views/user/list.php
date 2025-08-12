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
            <h2 class="mb-4">Users List</h2>
            <div class="col-md-12">
                <!-- Add New Item Button triggers modal -->
                <button class="btn btn-sm btn-orange" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fa fa-plus me-1"></i> Create New User
                </button>
                <!-- <button class="btn btn-sm btn-orange" data-bs-toggle="modal" data-bs-target="#uploadItemModal">
                    <i class="fa fa-upload me-1"></i> Upload Many 
                </button> -->

                <!-- Add Item Modal -->
                <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <form action="<?= base_url('user/add') ?>" enctype="multipart/form-data" method="post">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- start of row -->
                                    <div class="row justify-content-center align-items-start g-4">
                                        <!-- start of main col -->
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>First Name</label>
                                                    <input type="text" name="firstname"
                                                        class="form-control form-control-sm" required
                                                        placeholder="Enter first name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Last Name</label>
                                                    <input type="text" name="lastname"
                                                        class="form-control form-control-sm" required
                                                        placeholder="Enter last name">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Username</label>
                                                    <input type="text" name="username"
                                                        class="form-control form-control-sm" required
                                                        placeholder="Enter username">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Email</label>
                                                    <input type="email" name="email"
                                                        class="form-control form-control-sm" required
                                                        placeholder="Enter email">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Password</label>
                                                    <input type="password" name="password"
                                                        class="form-control form-control-sm"
                                                        placeholder="Enter password">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Role</label>
                                                    <select name="role" class="form-control form-control-sm">
                                                        <option value="" selected disabled>Select Role</option>
                                                        <option value="admin">Admin</option>
                                                        <option value="moderator">Moderator</option>
                                                        <option value="user">User</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>MBTI</label>
                                                    <select name="mbti" class="form-control form-control-sm">
                                                        <option value="" selected disabled>Select MBTI</option>
                                                        <option value="ENFJ">ENFJ - Protagonist (Charismatic and
                                                            inspiring leaders, able to mesmerize their listeners)
                                                        </option>
                                                        <option value="ENFP">ENFP - Campaigner (Enthusiastic, creative
                                                            and sociable free spirits, who can always find a reason to
                                                            smile)</option>
                                                        <option value="ENTJ">ENTJ - Commander (Bold, imaginative and
                                                            strong-willed leaders, always finding a way)</option>
                                                        <option value="ENTP">ENTP - Debater (Smart and curious thinkers
                                                            who cannot resist an intellectual challenge)</option>
                                                        <option value="ESFJ">ESFJ - Consul (Extraordinarily caring,
                                                            social and popular people, always eager to help)</option>
                                                        <option value="ESFP">ESFP - Entertainer (Spontaneous, energetic
                                                            and enthusiastic people – life is never boring around them)
                                                        </option>
                                                        <option value="ESTJ">ESTJ - Executive (Excellent administrators,
                                                            unsurpassed at managing things – or people)</option>
                                                        <option value="ESTP">ESTP - Entrepreneur (Smart, energetic and
                                                            very perceptive people, who truly enjoy living on the edge)
                                                        </option>
                                                        <option value="INFJ">INFJ - Advocate (Quiet and mystical, yet
                                                            very inspiring and tireless idealists)</option>
                                                        <option value="INFP">INFP - Mediator (Poetic, kind and
                                                            altruistic people, always eager to help a good cause)
                                                        </option>
                                                        <option value="INTJ">INTJ - Architect (Imaginative and strategic
                                                            thinkers, with a plan for everything)</option>
                                                        <option value="INTP">INTP - Logician (Innovative inventors with
                                                            an unquenchable thirst for knowledge)</option>
                                                        <option value="ISFJ">ISFJ - Defender (Very dedicated and warm
                                                            protectors, always ready to defend their loved ones)
                                                        </option>
                                                        <option value="ISFP">ISFP - Adventurer (Flexible and charming
                                                            artists, always ready to explore and experience something
                                                            new)</option>
                                                        <option value="ISTJ">ISTJ - Logistician (Practical and
                                                            fact-minded individuals, whose reliability cannot be
                                                            doubted)</option>
                                                        <option value="ISTP">ISTP - Virtuoso (Bold and practical
                                                            experimenters, masters of all kinds of tools)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Profile Image URL</label>
                                                    <input type="file" name="profileImage" accept="image/*"
                                                        class="form-control form-control-sm"
                                                        placeholder="https://example.com/image.jpg" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end of main col -->
                                    </div>
                                    <!-- end of row -->
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="mx-2 btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="mx-2 btn btn-orange">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="uploadItemModal" tabindex="-1" aria-labelledby="uploadItemModal"
                    aria-hidden="true">
                    <div class="modal-dialog" style="max-width: 95%; width: 95%;">
                        <div class="modal-content">
                            <form action="<?= base_url('item/addBulk') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title">Bulk Add Items</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="table-responsive" style="max-height: 60vh; overflow:auto;">
                                        <table class="table table-bordered align-middle text-center" id="itemsTable"
                                            style="min-width:2200px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Category ID</th>
                                                    <th>Item Name</th>
                                                    <th>Description</th>
                                                    <th>Image URL</th>
                                                    <th>Base Price</th>
                                                    <th>Rarity</th>
                                                    <th>Is Tradable</th>
                                                    <th>Is Buyable</th>
                                                    <th>Is Consumable</th>
                                                    <th>Is Stackable</th>
                                                    <th>Duration</th>
                                                    <th>Korean Name</th>
                                                    <th>Tier ID</th>
                                                    <th>Real Price</th>
                                                    <th>Discount %</th>
                                                    <th>Is Featured</th>
                                                    <th>Is On Sale</th>
                                                    <th>Quantity Available</th>
                                                    <th>Release Date</th>
                                                    <th>End Date</th>
                                                    <th>Thumbnail URL</th>
                                                    <th>Detail Images</th>
                                                    <th>Preview 3D Model</th>
                                                    <th>Attributes</th>
                                                    <th>Tags</th>
                                                    <th>Currency Type</th>
                                                    <th>Hunger Level</th>
                                                    <th>Energy Level</th>
                                                    <th>Hygiene Level</th>
                                                    <th>Health Level 1</th>
                                                    <th>Health Level 2</th>
                                                    <th>Happiness Level</th>
                                                    <th>Stress Level</th>
                                                    <th>Affinity</th>
                                                    <th>Experience</th>
                                                    <th>Pool ID</th>
                                                    <th>Drop Rate</th>
                                                    <th>Sub Category</th>
                                                    <th>Specie</th>
                                                    <th>Breed</th>
                                                    <th>Icon URL</th>
                                                    <th>Addressable URL</th>
                                                    <th>RGB Color</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="items[0][category_id]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:120px;"></td>
                                                    <td><input type="text" name="items[0][item_name]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:150px;"></td>
                                                    <td><input type="text" name="items[0][description]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:200px;"></td>
                                                    <td><input type="text" name="items[0][image_url]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="number" name="items[0][base_price]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][rarity]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_tradable]" value="1">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_buyable]" value="1">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_consumable]" value="1">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_stackable]" value="1">
                                                    </td>
                                                    <td><input type="text" name="items[0][duration]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][korean_name]"
                                                            class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="items[0][tier_id]"
                                                            class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="items[0][real_price]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][discount_percentage]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_featured]" value="1">
                                                    </td>
                                                    <td><input type="checkbox" name="items[0][is_on_sale]" value="1">
                                                    </td>
                                                    <td><input type="number" name="items[0][quantity_available]"
                                                            class="form-control form-control-sm" style="width:140px;">
                                                    </td>
                                                    <td><input type="date" name="items[0][release_date]"
                                                            class="form-control form-control-sm" style="width:160px;">
                                                    </td>
                                                    <td><input type="date" name="items[0][end_date]"
                                                            class="form-control form-control-sm" style="width:160px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][thumbnail_url]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][detail_images]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][preview_3d_model]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][attributes]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][tags]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:150px;"></td>
                                                    <td><input type="text" name="items[0][currency_type]"
                                                            class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="items[0][hunger_level]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][energy_level]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][hygiene_level]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][health_level_1]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][health_level_2]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][happiness_level]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][stress_level]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][affinity]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][experience]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][pool_id]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="number" name="items[0][drop_rate]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][sub_category]"
                                                            class="form-control form-control-sm" style="width:150px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][specie]"
                                                            class="form-control form-control-sm" style="width:150px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][breed]"
                                                            class="form-control form-control-sm" style="width:150px;">
                                                    </td>
                                                    <td><input type="text" name="items[0][icon_url]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][addressable_url]"
                                                            class="form-control form-control-sm"
                                                            style="min-width:180px;"></td>
                                                    <td><input type="text" name="items[0][rgb_color]"
                                                            class="form-control form-control-sm" style="width:120px;">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm mt-2" onclick="addRow()">+ Add
                                        Row</button>
                                </div>

                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-orange">Save Items</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    let rowIndex = 1;
                    function addRow() {
                        const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
                        const newRow = table.rows[0].cloneNode(true);
                        [...newRow.querySelectorAll('input')].forEach(input => {
                            let name = input.name.replace(/\d+/, rowIndex);
                            input.name = name;
                            if (input.type !== 'checkbox') input.value = '';
                            else input.checked = false;
                        });
                        table.appendChild(newRow);
                        rowIndex++;
                    }
                </script>


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
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Profile</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= esc($user['user_id']) ?></td>
                            <td><?= esc($user['username']) ?></td>
                            <td><?= esc($user['email']) ?></td>
                            <td><?= esc($user['first_name']) ?></td>
                            <td><?= esc($user['last_name']) ?></td>
                            <td>
                                <?php
                                $status = strtolower($user['status']);
                                $statusColor = 'secondary';
                                if ($status === 'active') {
                                    $statusColor = 'success';
                                } elseif ($status === 'inactive') {
                                    $statusColor = 'danger';
                                } elseif ($status === 'suspended') {
                                    $statusColor = 'warning';
                                }
                                ?>
                                <span class="text-capitalize badge bg-<?= $statusColor ?>"><?= esc($user['status']) ?></span>
                            </td>
                            <td>
                                <?php
                                $role = strtolower($user['role']);
                                $roleColor = 'secondary';
                                if ($role === 'admin') {
                                    $roleColor = 'danger';
                                } elseif ($role === 'moderator') {
                                    $roleColor = 'primary';
                                } elseif ($role === 'user') {
                                    $roleColor = 'warning';
                                }
                                ?>
                                <span class="text-capitalize badge bg-<?= $roleColor ?>"><?= esc($user['role']) ?></span>
                            </td>
                            </td>
                            <td>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?= esc($user['profile_image']) ?>" alt="User's Profile" width="40">
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Delete Button: Uses POST -->
                                <form action="<?= base_url('user/delete/' . esc($user['user_id'])) ?>" method="post"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn m-0 btn-sm btn-danger" data-bs-toggle="tooltip"
                                        title="Delete User"
                                        onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                <!-- Edit Button: Uses GET -->
                                <a href="<?= base_url('user/edit/' . esc($user['user_id'])) ?>" class="btn btn-sm btn-orange"
                                    data-bs-toggle="tooltip" title="Edit User">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <!-- Inventory Button: Uses GET -->
                                <a href="<?= base_url('user/inventory/' . esc($user['user_id'])) ?>" class="btn btn-sm btn-success"
                                    data-bs-toggle="tooltip" title="User Inventory">
                                    <i class="fa fa-box"></i>
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