<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script type="text/javascript">
<?php if (url_is('/tournaments/shared/*')) : ?>
apiURL = "<?= base_url('api/shared') ?>";
<?php else : ?>
apiURL = "<?= base_url('api') ?>";
<?php endif; ?>
const tournament_id = <?= $tournament['id'] ?>;
const markWinnerActionCode = '<?= BRACKET_ACTIONCODE_MARK_WINNER ?>';
const unmarkWinnerActionCode = '<?= BRACKET_ACTIONCODE_UNMARK_WINNER ?>';
const changeParticipantActionCode = '<?= BRACKET_ACTIONCODE_CHANGE_PARTICIPANT ?>';
const addParticipantActionCode = '<?= BRACKET_ACTIONCODE_ADD_PARTICIPANT ?>';
const deleteBracketActionCode = '<?= BRACKET_ACTIONCODE_DELETE ?>';
const hasEditPermission = '<?= (isset($_GET['mode']) && $_GET['mode'] == 'edit') || (isset($settings['target']) && ($settings['target'] == 'p' || $settings['target'] == 'a') && $settings['permission'] == 'e') || (isset($settings['users']) && auth()->user() && in_array(auth()->user()->id, explode(",", $settings['users'])) && $settings['permission'] == 'e' ) ? true : false ?>';
const isScoreEnabled = '<?= $tournament['score_enabled'] ?>';
const scoreBracket = parseInt(<?= ($tournament['score_bracket']) ? $tournament['score_bracket'] : 0 ?>)
const incrementScore = parseInt(<?= ($tournament['increment_score']) ? $tournament['increment_score'] : 0 ?>)
</script>
<script type="text/javascript">
let currentDescriptionDiv, newDescriptionContent, originalDescriptionContent
<?php if(!auth()->user()){ ?>
var dc = new Date();
dc.setTime(dc.getTime() + (24 * 60 * 60 * 1000));
document.cookie = 'tournament_id=<?= $tournament["id"] ?>;expires=' + dc.toUTCString() + ';path=/';
<?php }else{?>
document.cookie = 'tournament_id=;Max-Age=0'
<?php } ?>
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

    const descriptionPlaceholder = document.getElementById('descriptionPlaceholder')
    const appendDescription = (description, type) => {
        const wrapper = document.createElement('div')
        let editBtn = ''
        <?php if (auth()->user() && $tournament['user_id'] == auth()->user()->id && (isset($_GET['mode']) && $_GET['mode'] == 'edit')): ?>
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
    document.getElementById('confirmSaveButton').addEventListener('click', saveDescription)
    document.getElementById('confirmDismissButton').addEventListener('click', dismissEdit)
    <?php endif; ?>
    <?php if(!auth()->user()) : ?>
    $(document).on('click', function(e) {

        if (e.target.tagName == 'A' || e.target.parentElement.tagName == 'A') {
            e.preventDefault()

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
        $('#leaveConfirm').modal('hide')
    })
    <?php endif;?>
})
</script>

<script src="/js/brackets.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card col-12 shadow-sm" style="max-height: calc(100vh - 60px); overflow:scroll">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('tournaments') ?>">Dashboard</a></li>
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
            <?php if ($tournament['description']): ?>
            <button type="button" class="btn" id="toggleDescriptionBtn">
                <i class="fa-solid fa-book"></i>
            </button>
            <?php endif ?>

            <?php if($tournament['user_id'] == 0) :?>
            <button type="button" class="btn" id="toggleWarningBtn">
                <i class="fa-solid fa-warning"></i>
            </button>
            <?php endif; ?>
        </div>

        <?php if($tournament['user_id'] == 0) :?>
        <div id="warningPlaceholder"></div>
        <div id="warningMsg" class="d-none">
            <div class="text-center">⚠️ WARNING ⚠️</div>
            This tournament will only be available on the Tournament Gallery if visibility option was enabled; otherwise the tournament, alongside any progress, will be lost if the page is closed and you're not registered/loggedin!
            <br>
            If you didn't enable visibility setting in the tournament properties and would like to preserve the tournament and its progress, please Signup/Login and unlock much more features (such as controlling availability, visibility, sharing and music settings and more!) from your very own dedicated Tournament Dashboard available for registered users!
            <br>
            Note: Unaffiliated tournaments, meaning those created by unregistered visitors, will be deleted after 24 hours from the Tournament Gallery.
            <div class="text-center">
                <?php if(!auth()->user()): ?><br>
                <a href="<?= base_url('/login')?>" class="btn btn-primary">Signup/Login to preserve tournament</a>
                <?php endif;?>
            </div>
        </div>
        <?php endif;?>

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

        <div id="brackets" class="brackets p-5"></div>
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
                <button type="button" class="btn btn-danger leave">Discard</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($musicSettings) && $musicSettings) : ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($musicSettings[0]['start']) ? $musicSettings[0]['start'] : '' ?>" data-duration="<?= ($musicSettings[0]['duration']) ? $musicSettings[0]['duration'] : '' ?>">
    <source src="<?= ($musicSettings[0]['source'] == 'f') ? '/uploads/' . $musicSettings[0]['path'] : '/uploads/' . $musicSettings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>

<div class="buttons skipButtons">
    <button id="stopMusicButton" class="d-none">Pause Music</button>
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


<?= $this->endSection() ?>