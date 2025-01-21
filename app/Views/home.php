<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid text-center align-middle">
    <div class="home-title row d-flex justify-content-center p-3">
        <h1 class="p-5">Welcome!</h1>
    </div>

    <div class="row">
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center">
            <p class="home-content">
                <span>Get started by creating your first tournament!</span><br />
                <a class="btn btn-success mt-5" href="<?= base_url('/tournaments/create') ?>">Create Tournament</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center">
            <p class="home-content">
                <span>Here to spectate? Visit the Tournament Gallery!</span><br />
                <a class="btn btn-danger mt-5" href="<?= base_url('/gallery?filter=glr') ?>">Teleport to Gallery</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center">
            <p class="home-content">
                <span>Feeling the hype? Checkout the top contestants on the Participant Leaderboard!</span><br />
                <a class="btn btn-info light mt-5" href="<?= base_url('/participants') ?>">Check Leaderboard</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center">
            <p class="home-content">
                <span>Want to manage/customize your tournaments?</span><br />
                <span>Signup/Signin now to access your own dedicated Tournament Dashboard!</span><br />
                <a class="btn btn-warning mt-5" href="<?= base_url('/tournaments') ?>">My Tournament Dashboard</a>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>