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
        <h2 class="text-center mb-4 fw-bold">Edit Pet</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('pets/update/' . $pet['pet_id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row justify-content-center align-items-start m-5">
                <div class="col-md-10">
                    <div class="container border rounded p-3">
                        <h4 class="mb-4">Pet Information</h4>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Pet ID</label>
                                <input type="text" name="pet_id" class="form-control form-control-sm" required readonly
                                    value="<?= $pet['pet_id'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Owner's Name</label>
                                <input type="text" name="owner_name" class="form-control form-control-sm" required
                                    readonly value="<?= $pet['owner_name'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Pet Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" required
                                    value="<?= $pet['name'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="life_stage">Life Stage</label>
                                <select name="life_stage" class="form-control form-control-sm" required>
                                    <?php foreach ($life_stages as $stages): ?>
                                        <option value="<?= $stages['stage_id'] ?>"
                                            <?= $stages['stage_id'] == $pet['life_stage'] ? 'selected' : '' ?>>
                                            <?= $stages['stage_name'] ?>
                                            <?= $stages['stage_id'] == $pet['life_stage_id'] ? ' (Current) ' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Species</label>
                                <input type="text" name="species" class="form-control form-control-sm" readonly
                                    value="<?= ucfirst($pet['species']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Breed</label>
                                <input type="text" name="breed" class="form-control form-control-sm" readonly
                                    value="<?= $pet['breed'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Gender</label>
                                <select name="gender" class="form-control form-control-sm" required>
                                    <option value="0" <?= $pet['gender'] == '0' ? 'selected' : '' ?>>Male <?= $pet['gender'] == '0' ? ' (Current) ' : '' ?></option>
                                    <option value="1" <?= $pet['gender'] == '1' ? 'selected' : '' ?>>Female <?= $pet['gender'] == '1' ? ' (Current) ' : '' ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="personality">Personality</label>

                                <select name="personality" id="personality" class="form-control">
                                    <!-- default option -->
                                    <option value="" selected>Select Personality</option>

                                    <!-- Cat personalities -->
                                    <?php if ($pet['species'] == 'cat') { ?>
                                        <optgroup label="Cat">
                                            <?php foreach ($personalities['cat'] as $catPersonalities): ?>
                                                <option value="<?= $catPersonalities['trait_name'] ?>"
                                                    <?= $catPersonalities['trait_name'] == $pet['personality'] && $pet['species'] == 'cat' ? 'selected' : '' ?>>
                                                    <?= $catPersonalities['trait_name'] ?>        <?= $catPersonalities['trait_name'] == $pet['personality'] && $pet['species'] == 'cat' ? ' (Current)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php } elseif ($pet['species'] == 'dog') { ?>
                                        <!-- Dog personalities -->
                                        <optgroup label="Dog">
                                            <?php foreach ($personalities['dog'] as $dogPersonalities): ?>
                                                <option value="<?= $dogPersonalities['trait_name'] ?>"
                                                    <?= $dogPersonalities['trait_name'] == $pet['personality'] && $pet['species'] == 'dog' ? 'selected' : '' ?>>
                                                    <?= $dogPersonalities['trait_name'] ?>        <?= $dogPersonalities['trait_name'] == $pet['personality'] && $pet['species'] == 'dog' ? ' (Current)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="row justify-content-between">
                            <div class="col-md-2 text-center">
                                <label for="level">Level</label>
                                <p class="form-control-plaintext text-muted" id="level">
                                    <?= $pet['level'] ?>
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <label for="experience">Experience</label>
                                <p class="form-control-plaintext text-muted" id="experience">
                                    <?= $pet['experience'] ?>
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <label for="birthdate">Birthdate</label>
                                <p class="form-control-plaintext text-muted" id="birthdate">
                                    <?= date('Y-m-d', strtotime($pet['birthdate'])) ?>
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <label for="created_at">Created At</label>
                                <p class="form-control-plaintext text-muted" id="created_at">
                                    <?= date('Y-m-d H:i:s', strtotime($pet['created_at'])) ?>
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <label for="updated_at">Updated At</label>
                                <p class="form-control-plaintext text-muted" id="updated_at">
                                    <?= date('Y-m-d H:i:s', strtotime($pet['updated_at'])) ?>
                                </p>
                            </div>
                        </div>

                        <hr>
                        <!-- Submit button row -->
                        <div class="col-12 d-flex justify-content-center mt-3">
                            <a href="javascript:history.back()" class="mx-2 btn btn-danger">Back</a>
                            <button type="submit" class="mx-2 btn btn-orange">Update Pet</button>
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