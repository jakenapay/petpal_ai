<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <h2 class="mt-5 text-center">Petpal - Register</h2>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger mt-3 text-center">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success mt-3 text-center">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <form action="<?= base_url('register') ?>" method="post" id="registerForm">
                    <?= csrf_field() ?>
                    <div class="form-group mt-3">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="first_name"
                            value="<?= set_value('first_name') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="last_name"
                            value="<?= set_value('last_name') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" id="username"
                            value="<?= set_value('username') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email"
                            value="<?= set_value('email') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pass_confirm">Confirm Password</label>
                        <input type="password" class="form-control" name="pass_confirm" id="pass_confirm">
                    </div>
                    <button type="submit" class="btn btn-primary mt-4 w-100">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="<?= base_url('login'); ?>">Login</a></p>
            </div>
        </div>
    </div>

    <!-- Optional: Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>