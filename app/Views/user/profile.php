<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= base_url('/assets/css/style.css') ?>" rel="stylesheet">

</head>

<body>
    <?= $this->include('partials/sidebar') ?>
    <style>
        .profile-card {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .profile-card h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .rounded-image {
            width: 100px;
            height: 100px;
            border-radius: 50%; // this make circle
            overflow: hidden; //hiding image overflow
            object-position: center; // you can custom the position
        }

        .circular_image img {
            width: 100%;
        }
    </style>

    <div class="profile-card">
        <h2>Petpal - Profile</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3 text-center" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3 text-center" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('editProfile') ?>" enctype="multipart/form-data">
            <div class="text-center mb-3">
                <img class="img-fluid rounded-image"
                    src="<?= empty($user['profile_image']) ? '/assets/images/default_user_photo.jpg' : $user['profile_image'] ?>"
                    alt="Profile Image">
            </div>
            <div class="mb-3">
                <label for="profileImage" class="form-label"><strong>Upload Profile Image:</strong></label>
                <input type="file" class="form-control" id="profileImage" name="profile_image">
            </div>
            <input type="hidden" class="form-control" id="userId" name="user_id" value="<?= $user['user_id'] ?>"
                readonly>
            <div class="mb-3">
                <label for="email"><strong>Email Address:</strong></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?>">
            </div>
            <div class="mb-3">
                <label for="firstName"><strong>First Name:</strong></label>
                <input type="text" class="form-control" id="firstName" name="first_name"
                    value="<?= ucwords($user['first_name']) ?>">
            </div>
            <div class="mb-3">
                <label for="lastName"><strong>Last Name:</strong></label>
                <input type="text" class="form-control" id="lastName" name="last_name"
                    value="<?= ucwords($user['last_name']) ?>">
            </div>
            <div class="mb-3">
                <label for="status"><strong>Status:</strong></label>
                <select class="form-control" id="status" name="status">
                    <option value="active" <?= (strtolower($user['status']) == 'active') ? 'selected' : '' ?>>
                        Active <?= (strtolower($user['status']) == 'active') ? '(Current)' : '' ?>
                    </option>
                    <option value="inactive" <?= (strtolower($user['status']) == 'inactive') ? 'selected' : '' ?>>
                        Inactive <?= (strtolower($user['status']) == 'inactive') ? '(Current)' : '' ?>
                    </option>
                    <option value="suspended" <?= (strtolower($user['status']) == 'suspended') ? 'selected' : '' ?>>
                        Suspended <?= (strtolower($user['status']) == 'suspended') ? '(Current)' : '' ?>
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="role"><strong>Role:</strong></label>
                <input type="text" class="form-control" id="role" name="role" value="<?= $user['role'] ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="password">
                    <strong>Password:</strong>
                    <span style="font-size: 0.9rem; font-weight: normal; color: #6c757d;">(only enter if you wish to
                        update your password)</span>
                </label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="<?= base_url('logout') ?>" class="btn btn-secondary">Logout</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>

    <!-- Optional: Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>
</body>

</html>