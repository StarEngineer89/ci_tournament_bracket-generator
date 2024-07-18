<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title><?= $this->renderSection('title') ?></title>

        <!-- Bootstrap core CSS -->
        <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">

        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/fontawesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <?= $this->renderSection('pageStyles') ?>
        <link rel="stylesheet" href="/css/style.css">
    </head>

    <body class="bg-light">

        <main role="main" class="container-fluid">
            <div class="header d-grid gap-2 d-flex flex-wrap justify-content-end p-2">
                <?php if (auth()->user() && auth()->user()->id) : ?>
                <div class="notification-box me-3">
                    <?php $notificationService = service('notification'); ?>
                    <?php $notifications = $notificationService->getNotifications(auth()->user()->id) ?>
                    <button class="btn btn-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        <?php if (count($notifications)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= count($notifications) ?>
                            <span class="visually-hidden">unread messages</span>
                        </span>
                        <?php endif ?>
                    </button>
                    <?php if (count($notifications)): ?>
                    <ul class="dropdown-menu  dropdown-menu-end">
                        <?php foreach ($notifications as $notification): ?>
                        <li>
                            <p class="dropdown-item p-2">
                                <a class="" href="#" onclick="readNotification(this)" data-link="<?= base_url($notification['link']) ?>" data-id="<?= $notification['id'] ?>"><?= $notification['message'] ?></a>
                                <a class="delete" onclick="deleteNotification(this)" data-link="<?= base_url($notification['link']) ?>" data-id="<?= $notification['id'] ?>"><i class="fa fa-remove"></i></a>
                            </p>
                        </li>
                        <?php endforeach ?>
                    </ul>
                    <?php endif ?>
                </div>
                <?php endif ?>
                <?php if (auth()->user() && auth()->user()->id) : ?>
                <div class="d-flex">
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= auth()->user()->username ?>'s profile
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">General Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="<?php echo base_url('logout') ?>">Log out</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <div id="notificationAlertPlaceholder" class="position-relative"></div>

            <?= $this->renderSection('main') ?>
        </main>

        <!-- Modal -->
        <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="settingsModalLabel">General Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="settingsForm">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <p class="mb-0">All timestamps will be based on the following Timezone setting.</p>
                                    <p class="mb-0">Default is EST (time offset is −5 hours (UTC/GMT -5) during standard time and −4 hours (UTC/GMT -4) during daylight saving time)</p>
                                </div>
                            </div>
                            <div class="row">
                                <label for="timezone" class="form-label col-sm-3 col-form-label">Timezone</label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="timezone" name="<?= USERSETTING_TIMEZONE ?>">
                                        <!-- Timezone options will be populated here -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div id="timezoneInfo" class="col-md-9 offset-md-3">
                                    <div class="row">
                                        <p class="col-6 mb-0"><em>UTC time is: <span id="utcTime"></span></em></p>
                                        <p class="col-6 mb-0"><em>Local time is: <span id="localTime"></span></em></p>
                                        <p><em>Choose a city in the same timezone as you.</em></p>
                                        <p id="timezoneStatus" class="col-12 mb-0">This timezone is currently in <span id="currentTimeZoneLabel">standard time</span>.</p>
                                        <p id="daylightSaving" class="col-12">Daylight saving time begins on: <span id=""></span></p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveGeneralSettings" onclick="saveGeneralSettings(this)">Save</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Cookie Consent Modal -->
        <div id="cookieConsentModal" style="display:none; position:fixed; bottom:0; width:100%; background-color:#f1f1f1; padding:10px; text-align:center;">
            <p>This site uses cookies 🍪 to store information for the purpose of enhancing user experience. <br> If you reject cookies, you may experience limitations with functionality.</p>
            <button onclick="acceptCookies()">Accept</button>
            <button onclick="rejectCookies()">Reject</button>
        </div>

        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
        <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
        <script src="http://underscorejs.org/underscore-min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
        <script src="/js/functions.js"></script>
        <script type="text/javascript">
        let apiURL = "<?= base_url('api') ?>";

        var defaultTimezone = '<?= user_timezone(auth()->user()->id) ?>';
        $(document).ready(function() {
            const timezoneSelect = $('#timezone');
            timezoneSelect.val(defaultTimezone);
            updateTime(defaultTimezone);

            let currentYear = new Date().getFullYear();
            let dstStart = getSecondSundayOfMarch(currentYear, defaultTimezone);
            const formattedDate = formatDateToTimeZone(dstStart, defaultTimezone);
            $('#timezoneStatus').text(`This timezone is currently in ${defaultTimezone}.`);
            $('#daylightSaving').text(`Daylight saving time begins on: ${formattedDate}.`);

            <?php if (session()->getTempdata('welcome_message')) : ?>
            appendNotification('<?= session()->getTempdata('welcome_message') ?>', 'success');
            <?php session()->remove('welcome_message') ?>
            <?php endif; ?>
        })

        // Show the modal if cookie consent is not given
        if (!document.cookie.includes('cookie_consent')) {
            document.getElementById('cookieConsentModal').style.display = 'block';
        }
        </script>
        <?= $this->renderSection('pageScripts') ?>
    </body>

</html>