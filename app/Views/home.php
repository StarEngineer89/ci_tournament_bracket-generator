<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="text-center align-middle">
    <div class="d-flex justify-content-center mb-3">
        <h1>Welcome!<br>to<br>Tournament!</h1>
    </div>
    <div class="d-flex justify-content-center mb-3">
        <p class="home-content">
            Get started by creating your first tournament!<br />
            <a class="btn btn-success mt-3" href="<?= base_url('/tournaments/create') ?>">Create Tournament</a>
        </p>
    </div>
    <div class="d-flex justify-content-center mb-3">
        <p class="home-content">
            Here to spectate? Visit the Tournament Gallery!<br />
            <a class="btn btn-purple mt-3" href="<?= base_url('/gallery?filter=glr') ?>">Teleport to Gallery</a>
        </p>
    </div>
    <div class="d-flex justify-content-center mb-3">
        <p class="home-content">
            Feeling the hype? Checkout the top contestants on the Participant Leaderboard!<br />
            <a class="btn btn-orange mt-3" href="<?= base_url('/participants') ?>">Check Leaderboard</a>
        </p>
    </div>
    <div class="d-flex justify-content-center mb-3">
        <p class="home-content">
            Want to manage/customize your tournaments?<br />
            Signup/Signin now to access your own dedicated Tournament Dashboard!<br />
            <a class="btn btn-warning mt-3" href="<?= base_url('/tournaments') ?>">My Tournament Dashboard</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>