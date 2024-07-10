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
const hasEditPermission =
    '<?= session('share_permission') && session('share_permission') == SHARE_PERMISSION_VIEW ? false : true ?>';
</script>

<script type="text/javascript">
$(document).ready(function() {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="tournamentInfoAlert" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        alertPlaceholder.append(wrapper)
    }

    const alertTrigger = document.getElementById('liveAlertBtn')
    if (alertTrigger) {
        const msg = $('#liveAlertMsg').html();
        alertTrigger.addEventListener('click', () => {
            appendAlert(msg, 'success')
            alertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('tournamentInfoAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                alertTrigger.classList.remove('d-none')
            })
        })
    }

    $('#liveAlertBtn').click();
})
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card col-12 shadow-sm" style="max-height: calc(100vh - 60px); overflow:scroll">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Brackets</li>
            </ol>
        </nav>
        <h5 class="card-title d-flex justify-content-center mb-5">
            <? //= lang('Auth.login') ?><?= $tournament['name'] ?> Brackets
        </h5>

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

        <div class="container d-flex justify-content-end">
            <button type="button" class="btn" id="liveAlertBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2" />
                </svg>
            </button>
        </div>
        <div id="liveAlertPlaceholder"></div>
        <div id="liveAlertMsg" class="d-none">
            Note: <br />
            The tournament brackets are generated along a sequence of [2, 4, 8, 16, 32] in order to maintain bracket
            advancement integrity, otherwise there would be odd matchups that wouldn't make sense to the tournament
            structure.
            <?php if ((auth()->user() && auth()->user()->id == $tournament['user_id']) || (session('share_permission') && session('share_permission') == SHARE_PERMISSION_EDIT)) : ?>
            <br />
            You also have actions available to you by right clicking (or holding on mobile devices) the individual
            bracket box.
            <?php endif ?>
        </div>

        <div id="brackets" class="brackets p-5"></div>
    </div>
</div>

<?php if (isset($settings) && $settings) : ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($settings[0]['start']) ? $settings[0]['start'] : '' ?>" data-duration="<?= ($settings[0]['duration']) ? $settings[0]['duration'] : '' ?>">
    <source src="<?= ($settings[0]['source'] == 'f') ? '/uploads/' . $settings[0]['path'] : '/uploads/' . $settings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>

<button id="stopMusicButton" class="d-none">Stop Music</button>

<?php endif; ?>

<?= $this->endSection() ?>