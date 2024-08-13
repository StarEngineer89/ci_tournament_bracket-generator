<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url('profile/update-password') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $('#responseMessage').html(
                    `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                );
                if (response.success) {
                    $('#changePasswordForm')[0].reset(); // Reset form if successful
                }
            },
            error: function() {
                $('#responseMessage').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="text-center align-middle">
    <div class="d-flex justify-content-center">
        <div class="card col-12 shadow-sm" style="max-height: calc(100vh - 60px); overflow:scroll">
            <div class="card-body">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Information</li>
                    </ol>
                </nav>
                <h5 class="card-title d-flex justify-content-center mb-5">User Information</h5>

                <?php if (session('error') !== null) : ?>
                <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
                <?php elseif (session('errors') !== null) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php if (is_array(session('errors'))) : ?>
                    <?php foreach (session('errors') as $error) : ?>
                    <?= $error ?>
                    <br>
                    <?php endforeach ?>
                    <?php else : ?>
                    <?= session('errors') ?>
                    <?php endif ?>
                </div>
                <?php endif ?>

                <?php if (session('message') !== null) : ?>
                <div class="alert alert-success" role="alert"><?= session('message') ?><?= \Config\Services::validation()->listErrors() ?></div>
                <?php endif ?>

                <div class="container">
                    <form>
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-sm-3 col-form-label text-start">Email</label>
                            <div class="col-sm-6">
                                <input type="email" class="form-control" id="inputEmail3">
                            </div>
                            <div class="col-sm-3 text-start">
                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changeEmailModal">Change Email</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputPassword3" class="col-sm-3 col-form-label text-start">Password</label>
                            <div class="col-sm-3 text-start">
                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="changePasswordModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changePasswordModalLabel">Change Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" action="<?= site_url('profile/update-password') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3 pb-3 border-bottom">
                        <label for="current_password" class="form-label col-md-4 col-sm-12">Current Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="current_password" id="current_password" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="new_password" class="form-label col-md-4 col-sm-12">New Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="confirm_password" class="form-label col-md-4 col-sm-12">Confirm Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="changeEmailModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changeEmailModalLabel">Change Email</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-hint mb-3">
                    To change your account email, enter your current email address and the new email address you want to use. After clicking "Update Email," a verification code will be sent to your new email address. Enter the code in the field provided to finalize the update process.
                </div>
                <form action="<?= site_url('profile/update-email') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label for="current_email" class="form-label col-md-4 col-sm-12">Current Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" name="current_email" id="current_email" value="<?= auth()->user()->email ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="new_email" class="form-label col-md-4 col-sm-12">New Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" name="new_email" id="new_email" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Email</button>
                </form>
            </div>
            <div class="modal-footer visually-hidden">
                <form action="<?= base_url('profile/confirm-email-update') ?>" method="post">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control" name="verification_code" id="verification_code" required>
                        <div class="form-text">Enter the verification code sent to your new email address.</div>
                    </div>
                </form>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitPassword">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>