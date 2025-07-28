<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Item List</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="row">
            <h2 class="mb-4">Item List</h2>
            <div class="col-md-12">
                <!-- Add New Item Button triggers modal -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">Add New
                    Item</button>

                <!-- Add Item Modal -->
                <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl"> <!-- Make it extra large for 4 columns -->
                        <div class="modal-content">
                            <form action="<?= base_url('item/add') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row justify-content-center align-items-start g-4">
                                        <!-- Column 1 -->
                                        <div class="col-md-3">
                                            <label>Category ID <span class="text-danger">*</span></label>
                                            <select name="category_id" id="categorySelector"
                                                class="form-control form-control-sm">
                                                <?php foreach ($itemCategoriesData as $itemCategory): ?>
                                                    <option value="<?= $itemCategory['category_id']; ?>">
                                                        <?= $itemCategory['category_id']; ?> -
                                                        <?= $itemCategory['category_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Item Name <span class="text-danger">*</span></label>
                                            <input type="text" name="item_name" class="form-control form-control-sm"
                                                required placeholder="Sample Item">

                                            <label>Description</label>
                                            <textarea name="description" class="form-control form-control-sm"
                                                placeholder="Sample Description"></textarea>

                                            <label>Image URL</label>
                                            <input type="text" name="image_url" class="form-control form-control-sm"
                                                placeholder="https://example.com/image.jpg">

                                            <label>Base Price <span class="text-danger">*</span></label>
                                            <input type="number" name="base_price" class="form-control form-control-sm"
                                                required value="100">

                                            <label>Rarity <span class="text-danger">*</span></label>
                                            <select name="rarity" class="form-control form-control-sm">
                                                <?php foreach ($ItemRarityData as $itemRarity): ?>
                                                    <option value="<?= $itemRarity['rarity_id']; ?>">
                                                        <?= $itemRarity['rarity_id']; ?> -
                                                        <?= $itemRarity['rarity_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Is Tradable</label>
                                            <select name="is_tradable" class="form-control form-control-sm">
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No</option>
                                            </select>

                                            <label>Is Buyable</label>
                                            <input type="number" name="is_buyable" class="form-control form-control-sm"
                                                value="1">

                                            <label>Is Consumable</label>
                                            <select name="is_consumable" class="form-control form-control-sm">
                                                <option value="1">Yes</option>
                                                <option value="0" selected>No</option>
                                            </select>

                                            <label>Is Stackable</label>
                                            <select name="is_stackable" class="form-control form-control-sm">
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No</option>
                                            </select>

                                            <label>Duration</label>
                                            <input type="number" name="duration" class="form-control form-control-sm"
                                                value="0">

                                            <label>Korean Name</label>
                                            <input type="text" name="korean_name" class="form-control form-control-sm"
                                                value="샘플아이템">
                                        </div>

                                        <!-- Column 2 -->
                                        <div class="col-md-3">
                                            <label>Tier ID</label>
                                            <select name="tier_id" class="form-control form-control-sm">
                                                <?php foreach ($ItemTiersData as $itemTier): ?>
                                                    <option value="<?= $itemTier['tier_id']; ?>">
                                                        <?= $itemTier['tier_id']; ?> - <?= $itemTier['tier_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Real Price</label>
                                            <input type="number" step="0.01" name="real_price"
                                                class="form-control form-control-sm" value="0.00">

                                            <label>Discount Percentage</label>
                                            <input type="number" name="discount_percentage"
                                                class="form-control form-control-sm" value="0">

                                            <label>Is Featured</label>
                                            <select name="is_featured" class="form-control form-control-sm">
                                                <option value="1">Yes</option>
                                                <option value="0" selected>No</option>
                                            </select>

                                            <label>Is On Sale</label>
                                            <select name="is_on_sale" class="form-control form-control-sm">
                                                <option value="1">Yes</option>
                                                <option value="0" selected>No</option>
                                            </select>

                                            <label>Quantity Available</label>
                                            <input type="number" name="quantity_available"
                                                class="form-control form-control-sm" value="0">

                                            <label>Release Date</label>
                                            <input type="datetime-local" name="release_date"
                                                class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>">

                                            <label>End Date</label>
                                            <input type="datetime-local" name="end_date"
                                                class="form-control form-control-sm">

                                            <label>Thumbnail URL</label>
                                            <input type="text" name="thumbnail_url" class="form-control form-control-sm"
                                                value="https://example.com/thumb.jpg">

                                            <label>Detail Images</label>
                                            <input type="text" name="detail_images"
                                                class="form-control form-control-sm">

                                            <label>Preview 3D Model</label>
                                            <input type="text" name="preview_3d_model"
                                                class="form-control form-control-sm">

                                            <label>Attributes</label>
                                            <textarea name="attributes" class="form-control form-control-sm"
                                                placeholder="{}"></textarea>
                                        </div>

                                        <!-- Column 3 -->
                                        <div class="col-md-3">
                                            <label>Tags</label>
                                            <input type="text" name="tags" class="form-control form-control-sm"
                                                value="sample">

                                            <label>Currency Type</label>
                                            <select name="currency_type" class="form-control form-control-sm">
                                                <option value="coins" selected>Coins</option>
                                                <option value="diamonds">Diamonds</option>
                                            </select>

                                            <label>Hunger Level</label>
                                            <input type="number" step="0.01" max="100" name="hunger_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Energy Level</label>
                                            <input type="number" step="0.01" max="100" name="energy_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Hygiene Level</label>
                                            <input type="number" step="0.01" max="100" name="hygiene_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Health Level</label>
                                            <input type="number" step="0.01" max="100" name="health_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Happiness Level</label>
                                            <input type="number" step="0.01" max="100" name="happiness_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Stress Level</label>
                                            <input type="number" step="0.01" max="100" name="stress_level"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Affinity</label>
                                            <input type="number" step="0.01" max="100" name="affinity"
                                                class="form-control form-control-sm mb-3" value="5">

                                            <label>Experience</label>
                                            <input type="number" max="100" name="experience"
                                                class="form-control form-control-sm mb-3" value="0">

                                            <label>Pool ID</label>
                                            <select name="pool_id" class="form-control form-control-sm mb-3">
                                                <option value="">No Pool ID</option>
                                                <?php foreach ($poolData as $pool): ?>
                                                    <option value="<?= $pool['id']; ?>"><?= $pool['name']; ?> -
                                                        <?= $pool['id']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Drop Rate</label>
                                            <input type="number" step="0.001" max="100" name="drop_rate"
                                                class="form-control form-control-sm mb-3" value="0.000">
                                        </div>

                                        <!-- Column 4 for Item Accessories -->
                                        <div class="col-md-3" id="accessoriesColumn">
                                            <p class="text-mute">For item category that are accessories only.</p>
                                            <label>Subcategory</label>
                                            <select name="subCategory" id="subCategory"
                                                class="form-control form-control-sm">
                                                <?php foreach ($ItemSubCategoriesData as $subCat): ?>
                                                    <option value="<?= $subCat['id']; ?>"><?= $subCat['id']; ?> -
                                                        <?= $subCat['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Specie</label>
                                            <select name="specie" id="specie" class="form-control form-control-sm">
                                                <?php foreach ($specieData as $specie): ?>
                                                    <option value="<?= $specie['species_id']; ?>">
                                                        <?= $specie['species_id']; ?> - <?= $specie['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <label>Breed</label>
                                            <select name="breed" id="breed" class="form-control">
                                                <optgroup label="Cat Breeds">
                                                    <?php foreach ($petBreedData['catbreeds'] as $cat): ?>
                                                        <option value="<?= $cat->breed_id ?>"><?= $cat->breed_id ?> -
                                                            <?= $cat->breed_name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <optgroup label="Dog Breeds">
                                                    <?php foreach ($petBreedData['dogbreeds'] as $dog): ?>
                                                        <option value="<?= $dog->breed_id ?>"><?= $dog->breed_id ?> -
                                                            <?= $dog->breed_name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            </select>

                                            <label>Icon Url</label>
                                            <input type="text" name="iconUrl" id="iconUrl"
                                                class="form-control form-control-sm" placeholder="sample">

                                            <label>Addressable Url</label>
                                            <input type="text" name="addressableUrl" id="addressableUrl"
                                                class="form-control form-control-sm" placeholder="sample">

                                            <label>RGB Color</label>
                                            <input type="color" name="rgbColor" id="rgbColor"
                                                class="form-control form-control-sm" value="#ff0000">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="mx-2 btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="mx-2 btn btn-primary">Add Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 m-2">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert flash-message alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?> 

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert flash-message alert-success text-center"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <table id="itemTable" class="table table-sm table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Rarity</th>
                    <th>Base Price</th>
                    <th>Real Price</th>
                    <th>Final Price</th>
                    <th>Currency</th>
                    <th>Image</th>
                    <th>Buyable</th>
                    <th>Deleted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['item_id']) ?></td>
                            <td><?= esc($item['item_name']) ?></td>
                            <td><?= esc($item['rarity']) ?></td>
                            <td><?= esc($item['base_price']) ?></td>
                            <td><?= esc($item['real_price']) ?></td>
                            <td><?= esc($item['final_price']) ?></td>
                            <td><?= esc($item['currency_type']) ?></td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?= esc($item['image_url']) ?>" alt="Item" width="40">
                                <?php endif; ?>
                            </td>
                            <td><?= $item['is_buyable'] ? 'Yes' : 'No' ?></td>
                            <td><?= $item['is_deleted'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <!-- Delete Button: Uses POST -->
                                <form action="<?= base_url('item/delete/' . esc($item['item_id'])) ?>" method="post"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        title="Delete Item"
                                        onclick="return confirm('Are you sure you want to delete this item?');">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                <!-- Edit Button: Uses GET -->
                                <a href="<?= base_url('item/edit/' . esc($item['item_id'])) ?>" class="btn btn-sm btn-success"
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
            new DataTable('#itemTable');
        });

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

</body>

</html>