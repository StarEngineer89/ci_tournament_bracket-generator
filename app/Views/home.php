<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="text-center">
    <div class="d-flex justify-content-center"></div>
</div>
<?= $this->endSection() ?>