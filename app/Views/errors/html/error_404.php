<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?><?= lang('Errors.pageNotFound') ?><?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid text-center align-middle pt-5">
    <div class="wrap pt-5">
        <h1>404</h1>

        <p>
            <?php if (ENVIRONMENT !== 'production') : ?>
            <?= nl2br(esc($message)) ?>
            <?php else : ?>
            <?= lang('Errors.sorryCannotFind') ?>
            <?php endif; ?>
        </p>
    </div>

    <script type="text/javascript">
    setTimeout(function() {
        window.location.href = "/";
    }, 5000); // Redirect after 5 seconds
    </script>
</div>
<?= $this->endSection() ?>