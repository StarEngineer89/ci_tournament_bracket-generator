<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="text-center align-middle">
    <div class="d-flex justify-content-center">
        <h1>Welcome!<br>to<br>Tournament!</h1>
    </div>
</div>
<?= $this->endSection() ?>