<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/css/jquery.atwho.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tsparticles/confetti@3.0.3/tsparticles.confetti.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.rawgit.com/ichord/Caret.js/master/dist/jquery.caret.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/js/jquery.atwho.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
<?php if (url_is('/tournaments/shared/*')) : ?>
apiURL = "<?= base_url('api/shared') ?>";
<?php else : ?>
apiURL = "<?= base_url('api') ?>";
<?php endif; ?>
const tournament_id = <?= $tournament['id'] ?>;
const tournament_type = <?= intval($tournament['type']) ?>;
const KNOCKOUT_TOURNAMENT_TYPE = <?= TOURNAMENT_TYPE_KNOCKOUT ?>;
const markWinnerActionCode = '<?= BRACKET_ACTIONCODE_MARK_WINNER ?>';
const unmarkWinnerActionCode = '<?= BRACKET_ACTIONCODE_UNMARK_WINNER ?>';
const changeParticipantActionCode = '<?= BRACKET_ACTIONCODE_CHANGE_PARTICIPANT ?>';
const addParticipantActionCode = '<?= BRACKET_ACTIONCODE_ADD_PARTICIPANT ?>';
const removeParticipantActionCode = '<?= BRACKET_ACTIONCODE_REMOVE_PARTICIPANT ?>';
const deleteBracketActionCode = '<?= BRACKET_ACTIONCODE_DELETE ?>';
var hasEditPermission = <?= ($editable) ? 1 : 0 ?>;
var hasParticipantImageUpdatePermission = <?= $tournament['pt_image_update_enabled'] ? intval($tournament['pt_image_update_enabled']) : 0 ?>;
const isScoreEnabled = <?= $tournament['score_enabled'] ? 1 : 0 ?>;
const scoreBracket = parseInt(<?= ($tournament['score_bracket']) ? $tournament['score_bracket'] : 0 ?>);
const incrementScore = Number(<?= (intval($tournament['increment_score_enabled']) && $tournament['increment_score']) ? $tournament['increment_score'] : 0 ?>);
const incrementScoreType = '<?= (intval($tournament['increment_score_enabled']) && $tournament['increment_score_type']) ? $tournament['increment_score_type'] : TOURNAMENT_SCORE_INCREMENT_PLUS ?>';
let votingEnabled = <?= $votingEnabled ? $votingEnabled : 0 ?>;
let voteBtnAvailable = <?= $votingBtnEnabled ? $votingBtnEnabled : 0 ?>;
let votingMechanism = <?= $tournament['voting_mechanism'] ? intval($tournament['voting_mechanism']) : 1 ?>;
let allowHostOverride = <?= $tournament['allow_host_override'] ? $tournament['allow_host_override'] : 0 ?>;
let maxVoteCount = <?= $tournament['max_vote_value'] ? $tournament['max_vote_value'] : 0 ?>;
const votingMechanismRoundDurationCode = <?= EVALUATION_VOTING_MECHANISM_ROUND?>;
const votingMechanismMaxVoteCode = <?= EVALUATION_VOTING_MECHANISM_MAXVOTE?>;
const votingMechanismOpenEndCode = <?= EVALUATION_VOTING_MECHANISM_OPENEND?>;
let winnerAudioPlayingForEveryone = <?= $tournament['winner_audio_everyone'] ? $tournament['winner_audio_everyone'] : 0 ?>;
let initialUsers = <?= json_encode($users) ?>;

const is_temp_tournament = false;

const UUID = getOrCreateDeviceId()

let currentDescriptionDiv, newDescriptionContent, originalDescriptionContent

if (!location.href.includes('shared')) {
    <?php if(!auth()->user()){ ?>
    var dc = new Date();
    dc.setTime(dc.getTime() + (24 * 60 * 60 * 1000));
    document.cookie = 'device_id=' + UUID + 'tournament_id=<?= $tournament["id"] ?>;expires=' + dc.toUTCString() + ';path=/';
    <?php }else{?>
    document.cookie = 'device_id=' + UUID + 'tournament_id=;Max-Age=0'
    <?php } ?>
} else {
    if (parseInt(getCookie('tournament_id')) == tournament_id) hasEditPermission = true;
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
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

    const settingInfoAlertPlaceholder = document.getElementById('settingInfoAlertPlaceholder')
    const appendSettingInfoAlert = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="settingInfoAlert" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        settingInfoAlertPlaceholder.append(wrapper)
    }

    const settingInfoAlertTrigger = document.getElementById('settingInfoAlertBtn')
    if (settingInfoAlertTrigger) {
        const msg = $('#settingInfoAlertMsg').html();
        settingInfoAlertTrigger.addEventListener('click', () => {
            appendSettingInfoAlert(msg, 'success')
            settingInfoAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('settingInfoAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                settingInfoAlertTrigger.classList.remove('d-none')
            })
        })
    }

    $('#settingInfoAlertBtn').click();

    const warningPlaceholder = document.getElementById('warningPlaceholder')
    const appendWarning = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="tournamentWarning" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        warningPlaceholder.append(wrapper)
    }
    const warningTrigger = document.getElementById('toggleWarningBtn')
    if (warningTrigger) {
        const msg = $('#warningMsg').html();
        warningTrigger.addEventListener('click', () => {
            appendWarning(msg, 'warning')
            warningTrigger.classList.add('d-none')

            const warning = document.getElementById('tournamentWarning')
            warning.addEventListener('closed.bs.alert', event => {
                warningTrigger.classList.remove('d-none')
            })
        })
    }
    $('#toggleWarningBtn').click();


    <?php if ($tournament['description']): ?>
    const descriptionPlaceholder = document.getElementById('descriptionPlaceholder')
    const appendDescription = (description, type) => {
        const wrapper = document.createElement('div')
        let editBtn = ''
        <?php if (auth()->user() && $tournament['user_id'] == auth()->user()->id): ?>
        editBtn = '<button type="button" class="btn-edit" id="editDescriptionBtn" onclick="enableDescriptionEdit(this)"><i class="fa-solid fa-pen-to-square"></i></button>'
        <?php endif ?>
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="descriptionAlert" role="alert">`,
            `   <div class="description" id="description">${description}</div>`,
            editBtn,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        descriptionPlaceholder.append(wrapper)
    }

    const descriptionTrigger = document.getElementById('toggleDescriptionBtn')
    if (descriptionTrigger) {
        const description = $('#description').html();
        descriptionTrigger.addEventListener('click', () => {
            appendDescription(description, 'light')
            descriptionTrigger.classList.add('d-none')

            const myAlert = document.getElementById('descriptionAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                descriptionTrigger.classList.remove('d-none')
            })
        })
    }
    $('#toggleDescriptionBtn').click();
    <?php endif; ?>

    <?php $currentTime = new \DateTime() ?>
    <?php $startTime = new \DateTime($tournament['available_start']) ?>
    <?php $endTime = new DateTime($tournament['available_end']) ?>
    <?php $interval = $startTime->diff($endTime); ?>
    <?php $intervalStart = $currentTime->diff($startTime); ?>
    <?php $intervalEnd = $currentTime->diff($endTime); ?>

    <?php if ($tournament['availability']): ?>
    const availabilityAlertPlaceholder = document.getElementById('availabilityAlertPlaceholder')
    const appendAvailabilityAlert = (content, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="availabilityAlert" role="alert">`,
            `   <div class="availabilityAlert" id="availabilityAlertContent">${content}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        availabilityAlertPlaceholder.append(wrapper)
    }

    const availabilityAlertTrigger = document.getElementById('toggleVoteWarningBtn')
    if (availabilityAlertTrigger) {
        const msg = $('#availabilityAlertMsg').html();
        availabilityAlertTrigger.addEventListener('click', () => {
            appendAvailabilityAlert(msg, 'dark')
            availabilityAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('availabilityAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                availabilityAlertTrigger.classList.remove('d-none')
            })
        })
    }
    $('#toggleVoteWarningBtn').click();

    const countTimerAlertPlaceholder = document.getElementById('countTimerAlertPlaceholder')
    const appendCountTimerAlert = (content, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="countTimerAlert" role="alert">`,
            `   <div class="countTimerAlert" id="countTimerAlertContent">${content}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        countTimerAlertPlaceholder.append(wrapper)
    }

    const countTimerAlertTrigger = document.getElementById('countTimerNoteBtn')
    if (countTimerAlertTrigger) {
        const msg = $('#countTimerAlertMsg').html();
        countTimerAlertTrigger.addEventListener('click', () => {
            appendCountTimerAlert(msg, 'dark')
            countTimerAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('countTimerAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                countTimerAlertTrigger.classList.remove('d-none')
            })
        })
    }
    $('#countTimerNoteBtn').click();

    // Update the countdown timer
    let remainingTime = 0;
    <?php if ($currentTime < $startTime): ?>
    remainingTime = <?= strtotime($tournament['available_start']) - strtotime(date('Y-m-d H:i:s')) ?>;
    <?php endif; ?>

    <?php if ($currentTime >= $startTime && $currentTime < $endTime): ?>
    remainingTime = <?= strtotime($tournament['available_end']) - strtotime(datetime: date('Y-m-d H:i:s')) ?>;
    <?php endif; ?>

    function updateCountdown() {
        if (remainingTime <= 0) {
            <?php if ($currentTime < $startTime): ?>
            document.getElementById("availabilityTimer").innerHTML = "Tournament has started!";
            <?php endif; ?>

            <?php if ($currentTime >= $startTime && $currentTime < $endTime): ?>
            document.getElementById("availabilityTimer").innerHTML = "Tournament has ended!";
            <?php endif; ?>
            return;
        }

        let days = Math.floor(remainingTime / (60 * 60 * 24));
        let hours = Math.floor((remainingTime % (60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((remainingTime % (60 * 60)) / 60);
        let seconds = remainingTime % 60;

        document.getElementById("availabilityTimer").innerHTML =
            `${days}d ${hours}h ${minutes}m ${seconds}s`;

        remainingTime--;

        setTimeout(updateCountdown, 1000);
    }

    updateCountdown();
    <?php endif; ?>

    document.getElementById('confirmSaveButton').addEventListener('click', saveDescription)
    document.getElementById('confirmDismissButton').addEventListener('click', dismissEdit)

    <?php if(!auth()->user() && isset($editable) && $editable && !$tournament['user_id']) : ?>
    var leaveUrl;
    $(document).on('click', function(e) {

        if (e.target.tagName == 'A' || e.target.parentElement.tagName == 'A') {
            if (e.target.href.includes('login')) {
                return true
            }

            e.preventDefault();
            leaveUrl = e.target.href;

            // Show Bootstrap modal
            var modal = new bootstrap.Modal(document.getElementById('leaveConfirm'));
            modal.show();
        }
    })

    // Handle the modal confirmation
    document.getElementById('leaveToSignin').addEventListener('click', function() {
        // Allow the window/tab to close
        window.location.href = "/login"; // or use `window.close()` in some cases
    });

    $("#leaveConfirm .leave").on('click', function() {
        $('#leaveConfirm').modal('hide');
        location.href = leaveUrl;
    })
    <?php endif;?>

    if (hasEditPermission) {
        $(document).on("click", function(e) {
            if (!$(e.target.parentElement).hasClass('p-image')) $(".p-image").removeClass('active');
        })

    }

    if (hasEditPermission || hasParticipantImageUpdatePermission) {
        $(document).on("click", ".p-image img", function(e) {
            var pid = $(this).data('pid');
            if ($(this).hasClass('temp')) {
                $("#image_" + pid).trigger('click');
            } else {
                $(this).parent().addClass('active');
            }
        })
    }
})
</script>

<script src="/js/brackets.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="background">
    <div class="corner-top-left"></div>
    <div class="corner-top-right"></div>
    <div class="corner-bottom-left"></div>
    <div class="corner-bottom-right"></div>
    <div class="top-bg"></div>
    <div class="left-bg"></div>
    <div class="right-bg"></div>
    <div class="bottom-bg"></div>
</div>
<div class="card col-12 shadow-sm" style="min-height: calc(100vh - 60px);">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <a href="<?= $_SERVER['HTTP_REFERER'] ?? site_url('/') ?>"><i class="fa fa-angle-left"></i> Back</a>
            </ol>
        </nav>
        <h5 class="card-title d-flex justify-content-center mb-5">
            <? //= lang('Auth.login') ?><?= $tournament['name'] ?>
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

        <div class="container alert-btn-container mb-1 d-flex justify-content-end">
            <?php if ($tournament['availability']): ?>
            <button type="button" class="btn" id="countTimerNoteBtn">
                <i class="fa-solid fa-clock"></i>
            </button>
            <button type="button" class="btn" id="toggleVoteWarningBtn">
                <i class="fa-solid fa-calendar"></i>
            </button>
            <?php endif ?>

            <button type="button" class="btn" id="liveAlertBtn">
                <i class="fa-classic fa-solid fa-circle-exclamation fa-fw"></i>
            </button>

            <button type="button" class="btn" id="settingInfoAlertBtn">
                <i class="fa-solid fa-gear"></i>
            </button>

            <?php if ($tournament['description']): ?>
            <button type="button" class="btn" id="toggleDescriptionBtn">
                <i class="fa-solid fa-book"></i>
            </button>
            <?php endif ?>

            <?php if($tournament['user_id'] == 0 && isset($editable) && $editable) :?>
            <button type="button" class="btn" id="toggleWarningBtn">
                <i class="fa-solid fa-warning"></i>
            </button>
            <?php endif; ?>

            <button type="button" class="btn" id="viewQRBtn" onclick="displayQRCode()">
                <i class="fa fa-qrcode" aria-hidden="true"></i>
            </button>
        </div>

        <?php if ($tournament['availability']): ?>
        <div id="countTimerAlertPlaceholder"></div>
        <div id="countTimerAlertMsg" class="d-none">
            <span class="me-5"><strong>Tournament Duration: </strong><?= $tournament['available_start'] ?> - <?= $tournament['available_end'] ?> (<?= "{$interval->d} Days {$interval->h} Hours" ?>)</span>
            <?php if ($currentTime < $startTime): ?>
            <span class="ms-3"><strong>Time remaining until start: </strong><span class="timer" id="availabilityTimer"><?= "{$intervalStart->d} Days {$intervalStart->h}h {$intervalStart->m}m  {$intervalStart->s}s" ?></span></span>
            <?php endif; ?>

            <?php if ($currentTime > $startTime && $currentTime < $endTime): ?>
            <span class="ms-3"><strong>Time remaining until end: </strong><span class="timer" id="availabilityTimer"><?= "{$intervalEnd->d} Days {$intervalEnd->h}h {$intervalEnd->m}m  {$intervalEnd->s}s" ?></span></span>
            <?php endif; ?>

            <?php if ($currentTime > $endTime): ?>
            <strong>Completed</strong>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div id="availabilityAlertPlaceholder"></div>
        <div id="availabilityAlertMsg" class="d-none">
            The tournament <strong><?= $tournament['name'] ?></strong> will be available starting <?= auth()->user() ? convert_to_user_timezone($tournament['available_start'], user_timezone(auth()->user()->id)) : $tournament['available_start'] ?> and ending on <?= auth()->user() ? convert_to_user_timezone($tournament['available_end'], user_timezone(auth()->user()->id)) : $tournament['available_end'] ?>. <br />
            If voting is enabled, the voting period will begin once the tournament availability starts and conclude once the availability ends.
        </div>

        <?php if($tournament['user_id'] == 0 && isset($editable) && $editable) :?>
        <div id="warningPlaceholder"></div>
        <div id="warningMsg" class="d-none">
            <div class="text-center">⚠️ WARNING ⚠️</div>
            This tournament will only be available on the Tournament Gallery if visibility option was enabled; otherwise the tournament, alongside any progress, will be lost if the page is closed and you're not registered/loggedin!
            <br>
            If you didn't enable visibility setting in the tournament properties and would like to preserve the tournament and its progress, please Signup/Login and unlock much more features (such as controlling availability, visibility, sharing and audio settings and more!) from your very own dedicated Tournament Dashboard available for registered users!
            <br>
            Note: Unaffiliated tournaments, meaning those created by unregistered visitors, will be deleted after 24 hours from the Tournament Gallery.
            <div class="text-center">
                <?php if(!auth()->user()): ?><br>
                <a href="<?= base_url('/login')?>" class="btn btn-primary">Signup/Login to preserve tournament</a>
                <?php endif;?>
            </div>
        </div>
        <?php endif;?>

        <div id="settingInfoAlertPlaceholder"></div>
        <div id="settingInfoAlertMsg" class="d-none">
            <strong>Tournament Properties:</strong> <br />
            <strong>Elimination Type:</strong> <?= $tournament['type'] == TOURNAMENT_TYPE_SINGLE ? "Single" : ($tournament['type'] == TOURNAMENT_TYPE_DOUBLE ? "Double" : "Knockout") ?><br />
            <strong>Evaluation Method:</strong> <?= $tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL ? "Manual" : "Voting" ?><br />
            <?php if ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING): ?>
            &nbsp;&nbsp;<strong>Voting Accessibility:</strong> <?= $tournament['voting_accessibility'] == EVALUATION_VOTING_RESTRICTED ? "Restricted" : "Unrestricted" ?><br />
            &nbsp;&nbsp;<strong>Voting Mechanism:</strong> <?= $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE ? "Max Votes" : ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND ? "Round Duration" : "Open-Ended") ?><br />
            &nbsp;&nbsp;<strong>Retain vote count across rounds:</strong> <?= $tournament['voting_retain'] ? "On" : "Off" ?><br />
            &nbsp;&nbsp;<strong>Allow Host override:</strong> <?= $tournament['allow_host_override'] ? "On" : "Off" ?><br />
            <?php endif; ?>
            <strong>Participant Image Customization Access:</strong> <?= $tournament['pt_image_update_enabled'] ? "On" : "Off" ?><br />
            <strong>Audio for Final Winner:</strong> <?= $tournament['win_audio_enabled'] ? "On" : "Off" ?><br />
            <?php if ($tournament['winner_audio_everyone']): ?>
            &nbsp;&nbsp;<strong>Play for everyone:</strong> <?= $tournament['winner_audio_everyone'] ? "On" : "Off" ?><br />
            <?php endif; ?>
            <strong>Enable Scoring:</strong> <?= $tournament['score_enabled'] ? "On" : "Off" ?><br />
            <?php if ($tournament['score_enabled']): ?>
            &nbsp;&nbsp;<strong>Score per bracket per round:</strong> <?= $tournament['score_bracket'] ?><br />
            <?php endif; ?>
            &nbsp;&nbsp;<strong>Increment Score:</strong> <?= $tournament['increment_score_enabled'] ? "On" : "Off" ?><br />
            <?php if ($tournament['increment_score_enabled']): ?>
            &nbsp;&nbsp;&nbsp;&nbsp;<strong>Increment Type:</strong> <?= $tournament['increment_score_type'] == TOURNAMENT_SCORE_INCREMENT_PLUS ? "Plus" : "Multiply" ?><br />
            &nbsp;&nbsp;&nbsp;&nbsp;<strong>Increment Value:</strong> <?= $tournament['increment_score'] ?><br />
            <?php endif; ?>
        </div>

        <div id="liveAlertPlaceholder"></div>
        <div id="liveAlertMsg" class="d-none">
            Note: <br />
            The tournament brackets are generated along a sequence of [2, 4, 8, 16, 32] in order to maintain bracket advancement integrity, otherwise there would be odd matchups that wouldn't make sense to the tournament structure.
            <?php if ((auth()->user() && auth()->user()->id == $tournament['user_id']) || (session('share_permission') && session('share_permission') == SHARE_PERMISSION_EDIT)) : ?>
            You also have actions available to you by right clicking (or holding on mobile devices) the individual bracket box throughout the tournament availability window (assuming its set).<br>
            This limitation isn't applicable to the tournament host.<br>
            In other words, actions will be restricted for all after availability ends (e.g. if tournament is shared with edit permissions) except for the host, in which even if availability ends, the host would still be able to control actions.
            <br />
            <?php endif ?>
        </div>

        <div id="descriptionPlaceholder"></div>
        <div id="description" class="d-none">
            <?= $tournament['description'] ?>
        </div>

        <div id="brackets" class="brackets d-flex p-5"></div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="leaveConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">You're about to leave this page and thus will lose access to the tournament!</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You can preserve this tournament by signing up/signing in and accessing much more features from your very own dedicated Tournament Dashboard available for registered users!</p>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary signin" id="leaveToSignin">Signup/Signin to preserve tournament</button>
                <button type="button" class="btn btn-danger leave">Disregard and leave anyways</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($audioSettings) && $audioSettings) : ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($audioSettings[0]['start']) ? $audioSettings[0]['start'] : '' ?>" data-duration="<?= ($audioSettings[0]['duration']) ? $audioSettings[0]['duration'] : '' ?>">
    <source src="<?= ($audioSettings[0]['source'] == 'f') ? '/uploads/' . $audioSettings[0]['path'] : '/uploads/' . $audioSettings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>

<div class="buttons skipButtons">
    <button id="stopAudioButton" class="d-none">Pause Audio</button>
</div>
<?php endif; ?>


<!-- Save Confirmation Modal -->
<div class="modal fade" id="saveDescriptionConfirmModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveModalLabel">Confirm Save</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to save the changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSaveButton">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Dismiss Confirmation Modal -->
<div class="modal fade" id="dismissDescriptionEditConfirmModal" tabindex="-1" aria-labelledby="dismissModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dismissModalLabel">Confirm Discard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to discard the changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDismissButton">Discard</button>
            </div>
        </div>
    </div>
</div>

<!-- Display QR Modal -->
<div class="modal fade" id="displayQRCodeModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dismissModalLabel">Share/Scan the tournanent's QR code!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="qrcode" class="d-flex justify-content-center"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>