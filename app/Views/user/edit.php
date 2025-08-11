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
            font-size: 16px;
        }

        .modal-body .form-control {
            margin-bottom: 10px;
        }

        label {
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?= $this->include('partials/sidebar') ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4 fw-bold">PetPal - Edit User</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('user/update/' . $user['user_id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row justify-content-center align-items-start m-5">
                <!-- Main column -->
                <div class="col-md-4">
                    <div class="container border rounded p-3">
                        <!-- image preview -->
                        <div class="text-center">
                            <div
                                style="width:300px; height:300px; margin:auto; display:flex; align-items:center; justify-content:center; background:#f8f9fa; border-radius:8px; overflow:hidden;">
                                <img src="<?= $user['profile_image']; ?>" alt="User Image"
                                    style="max-width:100%; max-height:100%; object-fit:cover; display:block;">
                            </div>
                            <input type="file" name="profileImage" class="form-control form-control-sm mb-3 mt-3 my-5">
                            <small class="text-muted">Upload a new profile image (optional)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="container border rounded p-3">
                        <h4 class="mb-4">Profile Information</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <label>User ID</label>
                                <input type="text" name="userid" class="form-control form-control-sm" required readonly
                                    value="<?= esc($user['user_id']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control form-control-sm" required
                                    value="<?= esc($user['username']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm" required
                                    value="<?= esc($user['email']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control form-control-sm" required
                                    value="<?= esc($user['first_name']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Last Name</label>
                                <input type="text" name="lastname" class="form-control form-control-sm" required
                                    value="<?= esc($user['last_name']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label>MBTI</label>
                                <select name="mbti" class="form-control form-control-sm">
                                    <option value="" disabled <?= empty($user['mbti']) ? 'selected' : '' ?>>Select MBTI
                                    </option>
                                    <option value="ENFJ" <?= ($user['mbti'] == 'ENFJ') ? 'selected' : '' ?>>ENFJ -
                                        Protagonist
                                        (Charismatic and inspiring leaders, able to mesmerize their listeners)</option>
                                    <option value="ENFP" <?= ($user['mbti'] == 'ENFP') ? 'selected' : '' ?>>ENFP -
                                        Campaigner
                                        (Enthusiastic, creative and sociable free spirits, who can always find a reason
                                        to
                                        smile)</option>
                                    <option value="ENTJ" <?= ($user['mbti'] == 'ENTJ') ? 'selected' : '' ?>>ENTJ -
                                        Commander
                                        (Bold, imaginative and strong-willed leaders, always finding a way)</option>
                                    <option value="ENTP" <?= ($user['mbti'] == 'ENTP') ? 'selected' : '' ?>>ENTP - Debater
                                        (Smart and curious thinkers who cannot resist an intellectual challenge)
                                    </option>
                                    <option value="ESFJ" <?= ($user['mbti'] == 'ESFJ') ? 'selected' : '' ?>>ESFJ - Consul
                                        (Extraordinarily caring, social and popular people, always eager to help)
                                    </option>
                                    <option value="ESFP" <?= ($user['mbti'] == 'ESFP') ? 'selected' : '' ?>>ESFP -
                                        Entertainer
                                        (Spontaneous, energetic and enthusiastic people – life is never boring around
                                        them)
                                    </option>
                                    <option value="ESTJ" <?= ($user['mbti'] == 'ESTJ') ? 'selected' : '' ?>>ESTJ -
                                        Executive
                                        (Excellent administrators, unsurpassed at managing things – or people)</option>
                                    <option value="ESTP" <?= ($user['mbti'] == 'ESTP') ? 'selected' : '' ?>>ESTP -
                                        Entrepreneur
                                        (Smart, energetic and very perceptive people, who truly enjoy living on the
                                        edge)
                                    </option>
                                    <option value="INFJ" <?= ($user['mbti'] == 'INFJ') ? 'selected' : '' ?>>INFJ - Advocate
                                        (Quiet and mystical, yet very inspiring and tireless idealists)</option>
                                    <option value="INFP" <?= ($user['mbti'] == 'INFP') ? 'selected' : '' ?>>INFP - Mediator
                                        (Poetic, kind and altruistic people, always eager to help a good cause)</option>
                                    <option value="INTJ" <?= ($user['mbti'] == 'INTJ') ? 'selected' : '' ?>>INTJ -
                                        Architect
                                        (Imaginative and strategic thinkers, with a plan for everything)</option>
                                    <option value="INTP" <?= ($user['mbti'] == 'INTP') ? 'selected' : '' ?>>INTP - Logician
                                        (Innovative inventors with an unquenchable thirst for knowledge)</option>
                                    <option value="ISFJ" <?= ($user['mbti'] == 'ISFJ') ? 'selected' : '' ?>>ISFJ - Defender
                                        (Very dedicated and warm protectors, always ready to defend their loved ones)
                                    </option>
                                    <option value="ISFP" <?= ($user['mbti'] == 'ISFP') ? 'selected' : '' ?>>ISFP -
                                        Adventurer
                                        (Flexible and charming artists, always ready to explore and experience something
                                        new)</option>
                                    <option value="ISTJ" <?= ($user['mbti'] == 'ISTJ') ? 'selected' : '' ?>>ISTJ -
                                        Logistician
                                        (Practical and fact-minded individuals, whose reliability cannot be doubted)
                                    </option>
                                    <option value="ISTP" <?= ($user['mbti'] == 'ISTP') ? 'selected' : '' ?>>ISTP - Virtuoso
                                        (Bold and practical experimenters, masters of all kinds of tools)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="rewardpoint">Reward Point</label>
                                <input type="number" class="form-control" id="rewardpoint" name="rewardpoint"
                                    value="<?= $user['reward_point'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="status">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="" disabled>Select Status</option>
                                    <option value="active" <?= ($user['status'] == 'active') ? 'selected' : '' ?>>Active
                                    </option>
                                    <option value="inactive" <?= ($user['status'] == 'inactive') ? 'selected' : '' ?>>
                                        Inactive</option>
                                    <option value="suspended" <?= ($user['status'] == 'suspended') ? 'selected' : '' ?>>
                                        Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="role">Role</label>
                                <select name="role" class="form-control form-control-sm">
                                    <option value="" disabled>Select Role</option>
                                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin
                                    </option>
                                    <option value="moderator" <?= ($user['role'] == 'Moderator') ? 'selected' : '' ?>>
                                        Moderator
                                    </option>
                                    <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>User</option>
                                </select>
                            </div>
                        </div>

                        <h4 class="my-4">Game Details</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="number_of_pet">Number of Pets</label>
                                <input type="number" class="form-control" id="number_of_pet" name="number_of_pet"
                                    value="<?= $user['number_of_pet'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="experience">Experience</label>
                                <input type="number" class="form-control" id="experience" name="experience"
                                    value="<?= $user['experience'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="user_grade">User Grade</label>
                                <input type="text" class="form-control" id="user_grade" name="user_grade"
                                    value="<?= $user['user_grade'] ?>" required>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="coins">Coins</label>
                                <input type="number" class="form-control" id="coins" name="coins"
                                    value="<?= $user['coins'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="diamonds">Diamonds</label>
                                <input type="number" class="form-control" id="diamonds" name="diamonds"
                                    value="<?= $user['diamonds'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="total_real_money_spent">Total Real Money Spent</label>
                                <input type="text" class="form-control" id="total_real_money_spent"
                                    name="total_real_money_spent" value="<?= $user['total_real_money_spent'] ?>"
                                    readonly>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="created_at">Created At</label>
                                <p class="form-control-plaintext text-muted" id="created_at"><?= $user['created_at'] ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label for="updated_at">Updated At</label>
                                <p class="form-control-plaintext text-muted" id="updated_at"><?= $user['updated_at'] ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label for="last_login">Last Login</label>
                                <p class="form-control-plaintext text-muted" id="last_login"><?= $user['last_login'] ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label for="logout_time">Logout Time</label>
                                <p class="form-control-plaintext text-muted" id="logout_time">
                                    <?= $user['logout_time'] ?>
                                </p>
                            </div>
                        </div>

                        <hr>
                        <!-- Submit button row -->
                        <div class="col-12 d-flex justify-content-center mt-3">
                            <a href="<?= base_url('item') ?>" class="mx-2 btn btn-danger">Back</a>
                            <button type="submit" class="mx-2 btn btn-orange">Update</button>
                        </div>
                    </div>
                </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>

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