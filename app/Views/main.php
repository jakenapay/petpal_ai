<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Item - PetPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        li {
            list-style-type: none;
            padding-bottom: 2px;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h2 class="text-center mb-4">PetPal - Main</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center flash-message">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center flash-message">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center text-center">
            <div class="col-md-12 p-3">
                <h4>Links</h4>
                <li><a href="<?= base_url('profile'); ?>">Profile</a></li>
                <li><a href="<?= base_url('logout'); ?>">Logout</a></li>
                <li><a href="<?= base_url('register'); ?>">Register</a></li>
                <li><a href="<?= base_url('item/add'); ?>">Item Add</a></li>
            </div>
            <div class="w-50">
                <hr>
            </div>
            <div class="col-md-12 p-3">
                <h4>Under Maintenance</h4>
                <li><a href="<?= base_url(''); ?>">Item Update</a></li>
                <li><a href="<?= base_url(''); ?>">Item Delete</a></li>
                <li><a href="<?= base_url(''); ?>">Item Get</a></li>
            </div>
        </div>


    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <!-- JavaScript to hide flash messages after 5 seconds -->
    <script>
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