<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?= lang('Auth.emailActivateTitle') ?></h5>

            <?php if (session('error')) : ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif ?>

            <p><?= lang('Auth.emailActivateBody') ?></p>

            <form action="<?= url_to('auth-action-verify') ?>" method="post">
                <?= csrf_field() ?>

                <!-- Code -->
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="floatingTokenInput" name="token" placeholder="000000" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" value="<?= old('token') ?>" required>
                    <label for="floatingTokenInput"><?= lang('Auth.token') ?></label>
                </div>

                <!-- Resend Code Button -->
                <div class="text-end mb-2">
                    <button class="resend-verification-code-link btn btn-link" id="resend-code" onclick="sendVerificationCode()">Resend Code</b>
                </div>

                <div class="d-grid col-8 mx-auto m-3">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.send') ?></button>
                </div>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    startCooldown(resendButton);
});
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

let sendVerificationCode = () => {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    $.ajax({
        url: '<?= base_url('auth/resend-verification') ?>',
        type: 'get',
        success: function(response) {
            $('#responseMessage').html(
                `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
            );
            if (response.status == 'success') {
                $('.update-email-block').addClass('d-none')
                $('.confirm-code-block').removeClass('d-none')
            }

            startCooldown(resendButton);
        },
        error: function() {
            $('#responseMessage').html(
                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
            );
        }
    });
}
</script>
<?= $this->endSection() ?>