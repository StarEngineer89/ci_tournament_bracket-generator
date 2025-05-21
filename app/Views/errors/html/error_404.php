<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?><?= lang('Errors.pageNotFound') ?><?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript">
setTimeout(function() {
    window.location.href = "/";
}, 5000); // Redirect after 5 seconds
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="main-content container-fluid p-0">
    <div id="notificationAlertPlaceholder" class="position-fixed"></div>

    <div class="container-fluid text-center align-middle pt-5">
        <div class="wrap pt-5">
            <p>
            <h5>We couldn’t find that page, so we're redirecting you.</h5>
            </p>

            <br />

            <p>
                From there you can jump to whatever part of the site you want.
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>