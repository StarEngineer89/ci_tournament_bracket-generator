<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid align-middle">
    <div class="home-title row d-flex justify-content-center text-center p-5">
        <h1 class="p-3"><strong>Welcome to TournCreator!</strong></h1>
        <h5>Where Competition Meets Creativity!</h5>
    </div>

    <div class="row">
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <div class="home-content p-5">
                <p class="title text-center">
                    Build Epic Brackets with a Click, all for free!<br />
                </p>
                <div class="text-start">
                    Choose from various elimination types, customize themes, and decide how winners are determined — manually or through voting.<br />
                    Make your tournaments truly immersive with audio/video playback for dramatic matchups and thrilling finales.<br />
                    Plus, enjoy sleek animations that bring your brackets to life!
                </div>
                <p class="text-center"><a class="btn btn-success mt-5" href="<?= base_url('/tournaments/create') ?>">Create Tournament</a></p>
            </div>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Here to spectate? Visit the Tournament Gallery!</span><br />
                <a class="btn btn-danger mt-5" href="<?= base_url('/gallery?filter=glr') ?>">Teleport to Gallery</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Feeling the hype? Checkout the top contestants on the Participant Leaderboard!</span><br />
                <a class="btn btn-info light mt-5" href="<?= base_url('/participants') ?>">Check Leaderboard</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Want to manage/customize your tournaments?</span><br />
                <span>Signup/Signin now to access your own dedicated Tournament Dashboard!</span><br />
                <a class="btn btn-warning mt-5" href="<?= base_url('/tournaments') ?>">My Tournament Dashboard</a>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>