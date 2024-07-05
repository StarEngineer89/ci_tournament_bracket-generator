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
                <div class="d-flex"><a class="btn btn-primary" href="<?php echo base_url('logout') ?>">Log out</a></div>
                <?php endif; ?>
            </div>

            <div id="notificationAlertPlaceholder" class="position-relative"></div>

            <?= $this->renderSection('main') ?>
        </main>

        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
        <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
        <script src="http://underscorejs.org/underscore-min.js"></script>

        <script type="text/javascript">
        const appendAlert = (message, type) => {
            const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
            if (alertPlaceholder) {
                alertPlaceholder.innerHTML = ''
                const wrapper = document.createElement('div')

                if (Array.isArray(message)) {
                    wrapper.innerHTML = ''
                    message.forEach((item, i) => {
                        wrapper.innerHTML += [
                            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                            `   <div>${item}</div>`,
                            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                            '</div>'
                        ].join('')
                    })
                } else {
                    wrapper.innerHTML = [
                        `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                        `   <div>${message}</div>`,
                        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                        '</div>'
                    ].join('')
                }

                alertPlaceholder.append(wrapper)

                $("div.alert").fadeTo(5000, 500).slideUp(500, function() {
                    $("div.alert").slideUp(500);
                });
            }
        }

        const appendNotification = (message, type) => {
            const notificationPlaceholder = document.getElementById('notificationAlertPlaceholder')
            if (notificationPlaceholder) {
                notificationPlaceholder.innerHTML = ''
                const wrapper = document.createElement('div')

                if (Array.isArray(message)) {
                    wrapper.innerHTML = ''
                    message.forEach((item, i) => {
                        wrapper.innerHTML += [
                            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                            `   <div>${item}</div>`,
                            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                            '</div>'
                        ].join('')
                    })
                } else {
                    wrapper.innerHTML = [
                        `<div class="alert alert-${type} alert-dismissible position-fixed top-1 end-0 z-3 me-3 mt-1" role="alert">`,
                        `   <div class="d-flex">${message}</div>`,
                        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                        '</div>'
                    ].join('')
                }

                notificationPlaceholder.append(wrapper)

                $("div.alert").fadeTo(3000, 500).slideUp(500, function() {
                    $("div.alert").slideUp(500);
                });
            }

        }

        const readNotification = (notificationElement) => {
            const link = $(notificationElement).data('link')
            const notificationId = $(notificationElement).data('id')

            $.ajax({
                type: "put",
                url: `${apiURL}/notifications/mark-as-read/${notificationId}`,
                success: function(result) {
                    $(notificationElement).remove()
                    window.location.href = link
                },
                error: function(error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function() {
                    $("#overlay").fadeOut(300);
                }, 500);
            });
        }

        const deleteNotification = (notificationElement) => {
            const link = $(notificationElement).data('link')
            const notificationId = $(notificationElement).data('id')

            $.ajax({
                type: "delete",
                url: `${apiURL}/notifications/delete/${notificationId}`,
                success: function(result) {
                    $(notificationElement).remove()
                },
                error: function(error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function() {
                    $("#overlay").fadeOut(300);
                }, 500);
            });
        }
        </script>

        <script type="text/javascript">
        $(document).ready(function() {
            <?php if (session()->getTempdata('welcome_message')) : ?>
            appendNotification('<?= session()->getTempdata('welcome_message') ?>', 'success');
            <?php session()->remove('welcome_message') ?>
            <?php endif; ?>
        })
        </script>
        <?= $this->renderSection('pageScripts') ?>
    </body>

</html>