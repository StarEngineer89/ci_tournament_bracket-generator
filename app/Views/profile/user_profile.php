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
                    $('#responseMessage').html()
                    $('#notification-area').html(
                        `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                    );
                    $('#changePasswordForm')[0].reset(); // Reset form if successful
                    $('#changePasswordModal').modal('hide')
                }

                // Clear notification after 10 seconds
                setTimeout(function() {
                    $('#notification-area').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                    $('#responseMessage').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                }, 10000);
            },
            error: function() {
                $('#responseMessage').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    });
});

let sendVerificationCode = (resend = false) => {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    $.ajax({
        url: '<?= base_url('profile/update-email') ?>',
        type: 'POST',
        data: $('#updateEmailForm').serialize(),
        dataType: 'json',
        success: function(response) {
            $('#email-update-notification-area').html(
                `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
            );
            if (response.status == 'success') {
                $('.update-email-block').addClass('d-none')
                $('.confirm-code-block').removeClass('d-none')
            } else {
                $('#email-update-notification-area').html(
                    `<div class="alert alert-danger">${response.errors.new_email}</div>`
                );
            }

            startCooldown(resendButton);

            // Clear notification after 10 seconds
            setTimeout(function() {
                $('#email-update-notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
            }, 10000);
        },
        error: function() {
            $('#email-update-notification-area').html(
                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
            );
        }
    });
}

let startCooldown = (button) => {
    let cooldown = 60;
    let interval = setInterval(() => {
        if (cooldown <= 0) {
            clearInterval(interval);
            button.disabled = false;
            button.textContent = "Resend Code";
        } else {
            button.textContent = `Resend Code (${cooldown}s)`;
            cooldown--;
        }
    }, 1000);
}

let confirmVerificationCode = () => {
    $.ajax({
        url: '<?= base_url('profile/update-email-confirm') ?>',
        type: 'POST',
        data: $('#updateEmailForm').serialize(),
        success: function(response) {
            $('#notification-area').html(
                `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
            );
            if (response.status == 'success') {
                $('.update-email-block').addClass('d-none')
                $('.confirm-code-block').removeClass('d-none')
            } else {
                $('#email-update-notification-area').html(
                    `<div class="alert alert-danger">${response.message}</div>`
                );
            }

            // Clear notification after 10 seconds
            setTimeout(function() {
                $('#notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
                $('#email-update-notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
            }, 10000);
        },
        error: function() {
            $('#notification-area').html(
                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
            );
        }
    });
}

let deleteAccount = () => {
    window.location.href = "<?= base_url('close-account')?>"
}
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="d-flex justify-content-center" style="flex:auto;">
    <div class="card col-12 shadow-sm">
        <div class="card-body">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Information</li>
                </ol>
            </nav>
            <h5 class="card-title d-flex justify-content-center mb-5">User Information</h5>

            <div id="notification-area">
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
            </div>

            <div class="container">
                <form>
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-3 col-form-label text-start">Email</label>
                        <div class="col-sm-6">
                            <input type="email" class="form-control" id="inputEmail3" value="<?= $userInfo->email ?>" disabled>
                        </div>
                        <div class="col-sm-3 text-start">
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changeEmailModal">Change Email</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputPassword3" class="col-sm-3 col-form-label text-start">Password</label>
                        <div class="col-sm-6 text-start">
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="closeAccount" class="col-sm-3 col-form-label text-start">Close Account</label>
                        <div class="col-sm-6 text-start">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeAccountModal">Close Account</button>
                        </div>
                    </div>
                </form>
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
                <div class="mb-2" id="responseMessage"></div>
                <form id="changePasswordForm" action="<?= site_url('profile/update-password') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label for="new_password" class="form-label col-md-4 col-sm-12">New Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="password" id="new_password" required>
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
                <div id="email-update-notification-area"></div>
                <div class="text-hint mb-3">
                    To change your account email, enter the new email address you want to use. <br />
                    After clicking "Update Email," a verification code will be sent to your new email address. Enter the code in the field provided to finalize the update process.<br /><br />
                    Note: Check your spam folder in case the email may have been sent there and you're not seeing it in your primary inbox.<br />
                    If you still haven't received the verification code after a few minutes, it's possible you may have entered an invalid email address.
                </div>
                <form action="<?= site_url('profile/update-email-confirm') ?>" id="updateEmailForm" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label for="current_email" class="form-label col-md-4 col-sm-12">Current Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" name="current_email" id="current_email" value="<?= auth()->user()->email ?>" disabled>
                        </div>
                    </div>

                    <div class="update-email-block">
                        <div class="row mb-3">
                            <label for="new_email" class="form-label col-md-4 col-sm-12">New Email</label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" name="new_email" id="new_email" required>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100" id="sendVerificationCodeBtn" onclick="sendVerificationCode()">Update Email</button>
                    </div>

                    <div class="confirm-code-block d-none">
                        <div class="row mb-3">
                            <label for="new_email" class="form-label col-md-4 col-sm-12">Verification Code</label>
                            <div class="col-md-4 col-sm-6">
                                <input type="text" class="form-control" name="confirm_code" id="confirm_code" required>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <button class="resend-verification-code-link btn btn-primary" id="resend-code" onclick="sendVerificationCode()">Resend Code</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary w-100" id="confirmVerificationCodeBtn" onclick="confirmVerificationCode()">Confirm</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer visually-hidden">

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="closeAccountModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="closeAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="closeAccountModalLabel">⚠️ Confirm Account Deletion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h3>Are you sure you want to delete your account?</h3>
                <p>This action is permanent and cannot be undone.</p>
                <p>You will lose access to:</p>
                <p>
                    • All your tournaments<br />
                    • Voting records and leaderboard rankings<br />
                    • Any saved customizations and media<br />
                </p>

                This action cannot be reversed!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteAccount()">Close Account</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>