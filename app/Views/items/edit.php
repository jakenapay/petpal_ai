<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Item - PetPal</title>
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
        input.form-control,
        select.form-control,
        textarea.form-control {
            margin-bottom: 1rem;
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
    <div class="container mt-5">
        <h2 class="text-center mb-4">PetPal - Edit Item</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('item/update/' . $item['item_id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row justify-content-center align-items-start m-5">
                <!-- Column 1 -->
                <div class="col-md-3">
                    <label>Category ID <span class="text-danger">*</span></label>
                    <select name="category_id" id="categorySelector" class="form-control form-control-sm">
                        <?php foreach ($itemCategoriesData as $itemCategory): ?>
                            <option value="<?= $itemCategory['category_id']; ?>"
                                <?= $itemCategory['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                                <?= $itemCategory['category_id']; ?> -
                                <?= $itemCategory['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="item_name" class="form-control form-control-sm" required
                        value="<?= esc($item['item_name']) ?>">

                    <input type="hidden" name="item_id" class="form-control form-control-sm" required
                        value="<?= esc($item['item_id']) ?>">

                    <label>Description</label>
                    <textarea name="description" class="form-control form-control-sm"
                        placeholder="Sample Description"><?= esc($item['description']) ?></textarea>

                    <label>Image URL</label>
                    <input type="text" name="image_url" class="form-control form-control-sm"
                        value="<?= esc($item['image_url']) ?>">

                    <label>Base Price <span class="text-danger">*</span></label>
                    <input type="number" name="base_price" class="form-control form-control-sm" required
                        value="<?= esc($item['base_price']) ?>">

                    <label>Rarity <span class="text-danger">*</span></label>
                    <select name="rarity" class="form-control form-control-sm">
                        <?php foreach ($ItemRarityData as $itemRarity): ?>
                            <option value="<?= $itemRarity['rarity_id']; ?>"
                                <?= $itemRarity['rarity_id'] == $item['rarity'] ? 'selected' : '' ?>>
                                <?= $itemRarity['rarity_id']; ?> -
                                <?= $itemRarity['rarity_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Is Tradable</label>
                    <select name="is_tradable" class="form-control form-control-sm">
                        <option value="1" <?= $item['is_tradable'] == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $item['is_tradable'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                    <label>Is Buyable</label>
                    <input type="number" name="is_buyable" class="form-control form-control-sm"
                        value="<?= esc($item['is_buyable']) ?>">

                    <label>Is Consumable</label>
                    <select name="is_consumable" class="form-control form-control-sm">
                        <option value="1" <?= $item['is_consumable'] == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $item['is_consumable'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                    <label>Is Stackable</label>
                    <select name="is_stackable" class="form-control form-control-sm">
                        <option value="1" <?= $item['is_stackable'] == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $item['is_stackable'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                    <label>Duration</label>
                    <input type="number" name="duration" class="form-control form-control-sm"
                        value="<?= esc($item['duration']) ?>">

                    <label>Korean Name</label>
                    <input type="text" name="korean_name" class="form-control form-control-sm"
                        value="<?= esc($item['korean_name']) ?>">
                </div>

                <!-- Column 2 -->
                <div class="col-md-3">

                    <label>Tier ID</label>
                    <select name="tier_id" class="form-control form-control-sm">
                        <?php foreach ($ItemTiersData as $itemTier): ?>
                            <option value="<?= $itemTier['tier_id']; ?>"
                                <?= $itemTier['tier_id'] == $item['tier_id'] ? 'selected' : '' ?>>
                                <?= $itemTier['tier_id']; ?> -
                                <?= $itemTier['tier_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Real Price</label>
                    <input type="number" step="0.01" name="real_price" class="form-control form-control-sm"
                        value="<?= esc($item['real_price']) ?>">

                    <label>Discount Percentage</label>
                    <input type="number" name="discount_percentage" class="form-control form-control-sm"
                        value="<?= esc($item['discount_percentage']) ?>">

                    <label>Is Featured</label>
                    <select name="is_featured" class="form-control form-control-sm">
                        <option value="1" <?= $item['is_featured'] == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $item['is_featured'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                    <label>Is On Sale</label>
                    <select name="is_on_sale" class="form-control form-control-sm">
                        <option value="1" <?= $item['is_on_sale'] == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $item['is_on_sale'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                    <label>Quantity Available</label>
                    <input type="number" name="quantity_available" class="form-control form-control-sm"
                        value="<?= esc($item['quantity_available']) ?>">

                    <label>Release Date</label>
                    <input type="datetime-local" name="release_date" class="form-control form-control-sm"
                        value="<?= esc(date('Y-m-d\TH:i', strtotime($item['release_date'] ?? ''))) ?>">

                    <label>End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control form-control-sm"
                        value="<?= esc(date('Y-m-d\TH:i', strtotime($item['end_date'] ?? ''))) ?>">

                    <label>Thumbnail URL</label>
                    <input type="text" name="thumbnail_url" class="form-control form-control-sm"
                        value="<?= esc($item['thumbnail_url']) ?>">

                    <label>Detail Images</label>
                    <input type="text" name="detail_images" class="form-control form-control-sm"
                        value="<?= esc($item['detail_images']) ?>">

                    <label>Preview 3D Model</label>
                    <input type="text" name="preview_3d_model" class="form-control form-control-sm"
                        value="<?= esc($item['preview_3d_model']) ?>">

                    <label>Attributes</label>
                    <textarea name="attributes" class="form-control form-control-sm" placeholder="{}"><?= esc($item['attributes']) ?></textarea>
                </div>

                <!-- Column 3 -->
                <div class="col-md-3">

                    <label>Tags</label>
                    <input type="text" name="tags" class="form-control form-control-sm"
                        value="<?= esc($item['tags']) ?>">

                    <label>Currency Type</label>
                    <select name="currency_type" class="form-control form-control-sm">
                        <option value="coins" <?= $item['currency_type'] == 'coins' ? 'selected' : '' ?>>Coins</option>
                        <option value="diamonds" <?= $item['currency_type'] == 'diamonds' ? 'selected' : '' ?>>Diamonds</option>
                    </select>

                    <label>Hunger Level</label>
                    <input type="number" step="0.01" max="100" name="hunger_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['hunger_level']) ?>">

                    <label>Energy Level</label>
                    <input type="number" step="0.01" max="100" name="energy_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['energy_level']) ?>">

                    <label>Hygiene Level</label>
                    <input type="number" step="0.01" max="100" name="hygiene_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['hygiene_level']) ?>">

                    <label>Health Level</label>
                    <input type="number" step="0.01" max="100" name="health_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['health_level']) ?>">

                    <label>Happiness Level</label>
                    <input type="number" step="0.01" max="100" name="happiness_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['happiness_level']) ?>">

                    <label>Stress Level</label>
                    <input type="number" step="0.01" max="100" name="stress_level"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['stress_level']) ?>">

                    <label>Affinity</label>
                    <input type="number" step="0.01" max="100" name="affinity"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['affinity']) ?>">

                    <label>Experience</label>
                    <input type="number" max="100" name="experience"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['experience']) ?>">

                    <label>Pool ID</label>
                    <select name="pool_id" class="form-control form-control-sm mb-3">
                        <option value="">No Pool ID</option>
                        <?php foreach ($poolData as $pool): ?>
                            <option value="<?= $pool['id']; ?>" <?= $item['pool_id'] == $pool['id'] ? 'selected' : '' ?>>
                                <?= $pool['name']; ?> - <?= $pool['id']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Drop Rate</label>
                    <input type="number" step="0.001" max="100" name="drop_rate"
                        class="form-control form-control-sm mb-3" value="<?= esc($item['drop_rate']) ?>">

                </div>

                <!-- Column 4 for Item Accessories -->
                <div class="col-md-3" id="accessoriesColumn">
                    <p class="text-mute">For item category that are accessories only.</p>
                    <label>Subcategory</label>
                    <select name="subCategory" id="subCategory" class="form-control form-control-sm">
                        <?php foreach ($ItemSubCategoriesData as $subCat): ?>
                            <option value="<?= $subCat['id']; ?>"
                                <?= isset($itemAccessories['subcategory_id']) && $subCat['id'] == $itemAccessories['subcategory_id'] ? 'selected' : '' ?>>
                                <?= $subCat['id']; ?> -
                                <?= $subCat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Specie</label>
                    <select name="specie" id="specie" class="form-control form-control-sm">
                        <?php foreach ($specieData as $specie): ?>
                            <option value="<?= $specie['species_id']; ?>"
                                <?= isset($itemAccessories['species_id']) && $specie['species_id'] == $itemAccessories['species_id'] ? 'selected' : '' ?>>
                                <?= $specie['species_id']; ?> -
                                <?= $specie['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Breed <span class="fst-italic text-danger">Current: <?= esc($ItemAccessoriesData['breedName'] ?? '') ?></span></label>
                    <select name="breed" id="breed" class="form-control">
                        <option value="" selected>Select breed</option>
                        <optgroup label="Cat Breeds">
                            <?php foreach ($petBreedData['catbreeds'] as $cat): ?>
                                <option value="<?= $cat->breed_id ?>"
                                    <?= isset($ItemAccessoriesData['breed_id']) && $cat->breed_name == $ItemAccessoriesData['breedName'] ? 'selected' : '' ?>>
                                    <?= $cat->breed_id ?> - <?= $cat->breed_name ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Dog Breeds">
                            <?php foreach ($petBreedData['dogbreeds'] as $dog): ?>
                                <option value="<?= $dog->breed_id ?>" 
                                    <?= isset($ItemAccessoriesData['breedName']) && $dog->breed_name == $ItemAccessoriesData['breedName'] ? 'selected' : '' ?>>
                                    <?= $dog->breed_id ?> - <?= $dog->breed_name ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>

                    <label>Icon Url</label>
                    <input type="text" name="iconUrl" id="iconUrl" class="form-control form-control-sm"
                        value="<?= esc($ItemAccessoriesData['iconUrl'] ?? '') ?>">

                    <label>Addressable Url</label>
                    <input type="text" name="addressableUrl" id="addressableUrl" class="form-control form-control-sm"
                        value="<?= esc($ItemAccessoriesData['AddressableURL'] ?? '') ?>">

                    <label>RGB Color</label>
                    <input type="color" name="rgbColor" id="rgbColor" class="form-control form-control-sm"
                        value="<?= esc($ItemAccessoriesData['RGBColor'] ?? '#ff0000') ?>">

                </div>

                <!-- Submit button row -->
                <div class="col-12 d-flex justify-content-center mt-3">
                    <a href="<?= base_url('item/list') ?>" class="mx-2 btn btn-danger">Back</a>
                    <button type="submit" class="mx-2 btn btn-orange">Update Item</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script>
        document.getElementById('categorySelector').addEventListener('change', function () {
            const value = this.value;
            const fieldIds = ['subCategory', 'specie', 'breed', 'iconUrl', 'addressableUrl', 'rgbColor'];
            const shouldDisable = (value !== '1');
            fieldIds.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.disabled = shouldDisable;
                }
            });
        });
        document.getElementById('categorySelector').dispatchEvent(new Event('change'));
    </script>

</body>
</html>
