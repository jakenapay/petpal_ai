<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Delete Item - PetPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/assets/css/style.css') ?>">
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
        <h2 class="text-center mb-4">Delete Item</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('item/delete') ?>" method="post">
            <div class="row justify-content-center align-items-start m-5">
                <!-- Column 1 -->
                <div class="col-md-4">
                    <label>Item ID <span class="text-danger">*</span></label>
                    <input type="number" name="item_id" class="form-control form-control-sm" min="0" required>
                </div>

                <!-- Submit button row -->
                <div class="col-12 d-flex justify-content-center mt-3">
                    <button type="button" class="mx-2 btn btn-danger" onclick="history.back()">Back</button>
                    <button type="submit" class="mx-2 btn btn-primary">Delete</button>
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