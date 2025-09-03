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

        <form action="<?= base_url('user/item/edit/' . $inventoryItemData['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row justify-content-center align-items-start m-5">
                <!-- Column 1 -->
                <div class="col-md-6">
                    <!-- Column 1 -->
                    <label>Username</label>
                    <input type="hidden" value="<?= $inventoryItemData['user_id'] ?>"
                        class="form-control form-control-sm" required name="user_id" readonly>
                    <input type="text" value="<?= $user['username'] ?>" class="form-control form-control-sm"
                        required name="username" disabled>

                    <label>Item</label>
                    <input type="hidden" value="<?= $inventoryItemData['item_id'] ?>" class="form-control form-control-sm"
                        required name="item_id" readonly>
                    <input type="text" value="<?= $inventoryItemData['item_name'] ?>" class="form-control form-control-sm"
                        required name="item_name" disabled>

                    <label>Acquisitions</label>
                    <select name="acquisition_type_id" class="form-control form-control-sm">
                        <option value="" disabled selected>Select acquisition type</option>
                        <?php foreach ($allAcquisitions as $acquisition): ?>
                            <option value="<?= esc($acquisition['acquisition_type_id']) ?>"
                                <?= (isset($inventoryItemData['acquisition_type_id']) && $inventoryItemData['acquisition_type_id'] == $acquisition['acquisition_type_id']) ? 'selected' : '' ?>>
                                <?= esc($acquisition['type_name']) ?>
                                <?= (isset($inventoryItemData['acquisition_type_id']) && $inventoryItemData['acquisition_type_id'] == $acquisition['acquisition_type_id']) ? ' (Current)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>


                    <label>Quantity</label>
                    <input type="number" min="0" name="quantity" class="form-control form-control-sm" placeholder="0"
                        value="<?= esc($inventoryItemData['quantity'] ?? 0); ?>">

                    <label>Acquisition Date</label>
                    <input type="date" name="acquisition_date" class="form-control form-control-sm" required value="<?= esc(date('Y-m-d', strtotime($inventoryItemData['acquisition_date']))); ?>">

                    <label>Expiration Date</label>
                    <input type="date" name="expiration_date" class="form-control form-control-sm" value="<?= !empty($inventoryItemData['expiration_date']) ? esc(date('Y-m-d', strtotime($inventoryItemData['expiration_date']))) : ''; ?>">
                </div>

                <!-- Submit button row -->
                <div class="col-12 d-flex justify-content-center mt-3">
                    <a href="javascript:history.back()" class="mx-2 btn btn-danger">Back</a>
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