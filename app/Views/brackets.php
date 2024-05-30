<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="/js/brackets.js"></script>
<script type="text/javascript">
    <?php if (url_is('/tournaments/shared/*')) : ?>
        const apiURL = "<?= base_url('api/shared') ?>";
    <?php else : ?>
        const apiURL = "<?= base_url('api') ?>";
    <?php endif; ?>
    const tournament_id = <?= $tournament['id'] ?>;
    const markWinnerActionCode = '<?= BRACKET_ACTIONCODE_MARK_WINNER ?>';
    const unmarkWinnerActionCode = '<?= BRACKET_ACTIONCODE_UNMARK_WINNER ?>';
    const changeParticipantActionCode = '<?= BRACKET_ACTIONCODE_CHANGE_PARTICIPANT ?>';
    const addParticipantActionCode = '<?= BRACKET_ACTIONCODE_ADD_PARTICIPANT ?>';
    const deleteBracketActionCode = '<?= BRACKET_ACTIONCODE_DELETE ?>';
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card col-12 shadow-sm">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Brackets</li>
            </ol>
        </nav>
        <h5 class="card-title d-flex justify-content-center mb-5"><? //= lang('Auth.login') 
                                                                    ?><?= $tournament['name'] ?> Brackets</h5>

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
            <div class="alert alert-success" role="alert"><?= session('message') ?></div>
        <?php endif ?>

        <div class="container alert alert-success" role="alert">
            Note: <br />
            The tournament brackets are generated along a sequence of [2, 4, 8, 16, 32] in order to maintain bracket advancement integrity, otherwise there would be odd matchups that wouldn't make sense to the tournament structure.
            <br />
            You also have actions available to you by right clicking (or holding on mobile devices) the individual bracket box.
        </div>
        <div id="brackets" class="brackets d-flex justify-content-md-center justify-content-lg-center"></div>
    </div>
</div>

<?php if (isset($settings) && $settings && isset($settings[1])) : ?>
    <audio id="myAudio" preload="auto" data-starttime="<?= ($settings[1]['start']) ? $settings[1]['start'] : '' ?>" data-duration="<?= ($settings[1]['duration']) ? $settings[1]['duration'] : '' ?>">
        <source src="<?= ($settings[1]['source'] == 'f') ? '/uploads/' . $settings[1]['path'] : $settings[1]['path'] ?>" type="audio/mpeg" id="audioSrc">
    </audio>
<?php else : ?>
    <audio id="myAudio" preload="auto">
        <source src="https://youtu.be/Gb1iGDchKYs?si=fT3fFBreaYw_bh4l" type="audio/mpeg" id="audioSrc">
    </audio>
<?php endif; ?>



<?= $this->endSection() ?>