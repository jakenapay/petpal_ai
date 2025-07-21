<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Item - PetPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        input.form-control,
        select.form-control,
        textarea.form-control {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h2 class="text-center mb-4">PetPal - Add Item</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('item/add') ?>" method="post">
            <div class="row justify-content-center align-items-start m-5">
                <!-- Column 1 -->
                <div class="col-md-4">
                    <label>Category ID <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-control form-control-sm">
                        <?php foreach ($itemCategoriesData as $itemCategory): ?>
                            <option value="<?= $itemCategory['category_id']; ?>"><?= $itemCategory['category_id']; ?> -
                                <?= $itemCategory['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- <input type="number" n ame="category_id" class="form-control form-control-sm" required value="1"> -->

                    <label>Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="item_name" class="form-control form-control-sm" required
                        placeholder="Sample Item">

                    <label>Description</label>
                    <textarea name="description" class="form-control form-control-sm"
                        placeholder="Sample Description"></textarea>

                    <label>Image URL</label>
                    <input type="text" name="image_url" class="form-control form-control-sm"
                        placeholder="https://example.com/image.jpg">

                    <label>Base Price <span class="text-danger">*</span></label>
                    <input type="number" name="base_price" class="form-control form-control-sm" required value="100">

                    <label>Rarity <span class="text-danger">*</span></label>
                    <select name="rarity" class="form-control form-control-sm">
                        <?php foreach ($ItemRarityData as $itemRarity): ?>
                            <option value="<?= $itemRarity['rarity_id']; ?>"><?= $itemRarity['rarity_id']; ?> -
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
                    <input type="number" name="is_buyable" class="form-control form-control-sm" value="1">

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
                    <input type="number" name="duration" class="form-control form-control-sm" value="0">

                    <label>Korean Name</label>
                    <input type="text" name="korean_name" class="form-control form-control-sm" value="샘플아이템">
                </div>

                <!-- Column 2 -->
                <div class="col-md-4">

                    <label>Tier ID</label>
                    <!-- ItemTiersData -->
                    <select name="tier_id" class="form-control form-control-sm">
                        <?php foreach ($ItemTiersData as $itemTier): ?>
                            <option value="<?= $itemTier['tier_id']; ?>"><?= $itemTier['tier_id']; ?> -
                                <?= $itemTier['tier_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- <input type="number" name="tier_id" class="form-control form-control-sm" value="1"> -->

                    <label>Real Price</label>
                    <input type="number" step="0.01" name="real_price" class="form-control form-control-sm"
                        value="0.00">

                    <label>Discount Percentage</label>
                    <input type="number" name="discount_percentage" class="form-control form-control-sm" value="0">

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
                    <input type="number" name="quantity_available" class="form-control form-control-sm" value="0">

                    <label>Release Date</label>
                    <input type="datetime-local" name="release_date" class="form-control form-control-sm"
                        value="<?= date('Y-m-d\TH:i') ?>">

                    <label>End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control form-control-sm">

                    <label>Thumbnail URL</label>
                    <input type="text" name="thumbnail_url" class="form-control form-control-sm"
                        value="https://example.com/thumb.jpg">

                    <label>Detail Images</label>
                    <input type="text" name="detail_images" class="form-control form-control-sm">
                    <!-- <textarea name="detail_images" class="form-control form-control-sm"></textarea> -->

                    <label>Preview 3D Model</label>
                    <input type="text" name="preview_3d_model" class="form-control form-control-sm">
                    
                    <label>Attributes</label>
                    <textarea name="attributes" class="form-control form-control-sm" placeholder="{}"></textarea>
                </div>

                <!-- Column 3 -->
                <div class="col-md-4">

                    <label>Tags</label>
                    <input type="text" name="tags" class="form-control form-control-sm" value="sample">

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
                    <input type="number" step="0.01" max="100" name="affinity" class="form-control form-control-sm mb-3"
                        value="5">

                    <label>Experience</label>
                    <input type="number" max="100" name="experience" class="form-control form-control-sm mb-3"
                        value="0">

                    <label>Pool ID</label>
                    <select name="pool_id" class="form-control form-control-sm mb-3">
                        <option value="">No Pool ID</option>
                        <?php foreach ($poolData as $pool): ?>
                            <option value="<?= $pool['id']; ?>"><?= $pool['name']; ?> - <?= $pool['id']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Drop Rate</label>
                    <input type="number" step="0.001" max="100" name="drop_rate"
                        class="form-control form-control-sm mb-3" value="0.000">

                </div>

                <!-- Submit button row -->
                <div class="col-12 d-flex justify-content-center mt-3">
                    <button type="button" class="mx-2 btn btn-danger" onclick="history.back()">Back</button>
                    <button type="submit" class="mx-2 btn btn-primary">Add Item</button>
                </div>
            </div>
        </form>



    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>