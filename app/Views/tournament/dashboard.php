<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Dashboard<?= $this->endSection() ?>

<?= $this->section('main') ?>


<div class="nav nav-tabs nav-underline" role="tablist">
    <a class="nav-link <?= ($navActive == 'all') ? 'active' : '' ?>" id="v-pills-home-tab" href="<?= base_url('tournaments')?>">Current Tournaments</a>
    <a class="nav-link <?= ($navActive == 'archived') ? 'active' : '' ?>" id="v-pills-profile-tab" href="<?= base_url('tournaments?filter=archived')?>">Archived Tournaments</a>
    <a class="nav-link <?= ($navActive == 'shared') ? 'active' : '' ?>" id="v-pills-settings-tab" href="<?= base_url('tournaments?filter=shared')?>">Shared Tournaments</a>
</div>

<div class="card col-12 shadow-sm">
    <div class="card-body container">
        <h5 class="card-title d-flex justify-content-center">
            <? //= lang('Auth.login') ?>Tournament Dashboard
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

        <div id="liveAlertPlaceholder"></div>

        <div class="" id="tournamentsTableWrapper">
            <?php echo $table ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Are you sure you want to delete this tournament "<span class="tournament-name"></span>"?</h4>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="resetModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to reset this tournament "<span class="tournament-name"></span>"?</h5>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmReset">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Tournament Music Settings</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="tournamentForm" method="POST" endtype="multipart/form-data">
                    <div id="music-settings-panel">
                        <?= $musicSettingsBlock ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Share Tournament "<span class="tournament-name"></span>"</h1><br />

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="mb-3">Select a sharing option, generate url, and click save to share the
                    tournament.</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usertype" id="share-public" value="<?= SHARE_TO_PUBLIC ?>" data-target="Public">
                    <label class="form-check-label" for="share-public">
                        Public on the web
                        <div class="form-text">Anyone on the internet can find and access. No sign-in required</div>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usertype" id="share-guest" value="<?= SHARE_TO_EVERYONE ?>" data-target="Anyone">
                    <label class="form-check-label" for="share-guest">
                        Anyone with the link
                        <div class="form-text">Anyone who has the link can access. (Signin required) </div>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="usertype" id="share-users" value="<?= SHARE_TO_USERS ?>" data-target="Private" checked>
                    <label class="form-check-label" for="share-users">
                        Private
                        <div class="form-text">Only people explicitly granted</div>
                    </label>
                </div>
                <form id="privateUserTagsInputForm" method="post" class="form-horizontal">
                    <div class="private-users input-group row gy-2 gx-3 align-items-center mb-3">
                        <label for="userTagsInput" class="form-label col-form-label col-sm-4">Share with</label>
                        <div class="col-sm-8"><input type="text" id="userTagsInput" name="private-users" class="form-control" placeholder="Enter registered username(s)" required /></div>
                    </div>
                </form>
                <div class="input-group row gy-2 gx-3 align-items-center mb-3">
                    <label class="form-label col-form-label col-sm-4">Access: <span class="selected-target">Private</span></label>
                    <div class="col-auto">
                        <select class="form-select" name="permission" aria-label="Access permission">
                            <option value="<?= SHARE_PERMISSION_EDIT ?>">Can edit</option>
                            <option value="<?= SHARE_PERMISSION_VIEW ?>" selected>Can view</option>
                        </select>
                    </div>
                    <div id="sharePermissionHelpBlock" class="form-text"></div>
                </div>
                <div class="share-url row mb-3 d-flex flex-row-reverse">
                    <div class="col-auto input-group">
                        <input type="text" class="form-control" id="tournamentURL" value="" aria-label="Tournament URL" aria-describedby="urlCopy" readonly>
                        <button class="btn btn-outline-secondary input-group-text" type="button" id="urlCopyBtn" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-content="Link Copied!">Copy</button>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:;" onClick="generateURL()">Generate URL</a>
                    </div>
                    <div class="dropdown">
                        <a class="btn btn-primary" data-bs-target="#shareHistoryModal" data-bs-toggle="modal">
                            View Sharing(s)
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmShare">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareHistoryModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Tournament "<span class="tournament-name"></span>"
                    Sharings</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="share-settings table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" class="resizable">URL</th>
                                <th scope="col">Created</th>
                                <th scope="col">Modified</th>
                                <th scope="col">Accessiblility</th>
                                <th scope="col">Permission</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="close-share-history btn btn-secondary" data-bs-target="#shareModal" data-bs-toggle="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="purgeShareConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to purge this link?</h5>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmPurgeShare">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewLogModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">History of Actions</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="action-history table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">Time</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkActionConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="bulkActionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="resetModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="confirm-msg mb-3 text-center"></div>
                <div class="input-wrapper mb-3 d-flex justify-content-center"></div>
                <h5 class="mt-4 text-center">Are you sure you want to proceed? <span class="text-danger">This action cannot be undone!</span></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-danger" id="confirmBulkActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.1/js/bootstrapValidator.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script src="/js/participants.js"></script>
<script type="text/javascript">
let apiURL = "<?= base_url('api') ?>";

var users_json = '<?= json_encode($users) ?>';

var table = null;
var datatableRows;

//get data pass to json
var task = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("username"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: {
        url: apiURL + '/tournaments/fetchUsersList',
        filter: function(list) {
            return $.map(list, function(username) {
                return {
                    name: username
                };
            });
        }
    },
    local: jQuery.parseJSON(users_json), //you can use json type
    // remote: {
    //     url: apiURL + '/tournaments/fetchUsersList',
    //     prepare: function(query, settings) {
    //         settings.type = 'POST';
    //         settings.contentType = 'application/json';
    //         settings.data = JSON.stringify({
    //             query: query
    //         });
    //         console.log(query);
    //         return settings;
    //     },
    //     transform: function(response) {
    //         // Process the response to fit the expected format if needed
    //         return response;
    //     }
    // }
});

task.initialize();

$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip()

    $('#confirmReset').on('click', function() {
        const tournament_id = resetModal.getAttribute('data-id');
        $.ajax({
            type: "GET",
            url: apiURL + '/tournaments/' + tournament_id + '/clear',
            success: function(result) {
                $('#resetConfirm').modal('hide');
                setTimeout(() => {
                    alert("Brackets was cleared successfully.")
                }, 500);;
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    <?php if ($navActive == 'shared'): ?>
    <?php if ($shareType == 'wh'): ?>
    var orderFalseColumns = [0, 2, 3, 4, 5]
    <?php else: ?>
    var orderFalseColumns = [0, 2, 3, 5]
    <?php endif ?>
    table = $('#tournamentTable').DataTable({
        "order": [
            [1, "asc"]
        ], // Initial sorting by the first column ascending
        "paging": true, // Enable pagination
        "searching": true, // Enable search box
        "columnDefs": [{
            "orderable": false,
            "targets": orderFalseColumns
        }],
    });

    $('#typeFilter').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        table.columns(2).search(selectedType).draw();
    });

    $('#stautsFilter').on('change', function() {
        var selectedStatus = $(this).val().toLowerCase();
        table.columns(3).search(selectedStatus).draw();
    });
    $('#accessibilityFilter').on('change', function() {
        var selectedPermission = $(this).val().toLowerCase();
        table.columns(4).search(selectedPermission).draw();
    });

    $('#userByFilter').on('change', function() {
        var selectedUser = $(this).val().toLowerCase().trim();
        table.columns(5).search(selectedUser).draw();
    });
    <?php else: ?>
    table = $('#tournamentTable').DataTable({
        "order": [
            [1, "asc"]
        ], // Initial sorting by the first column ascending
        "paging": true, // Enable pagination
        "searching": true, // Enable search box
        "columnDefs": [{
            "orderable": false,
            "targets": [0, 3, 4, 6]
        }],
        // Add custom initComplete to initialize select all checkbox
        "initComplete": function(settings, json) {
            // Add a select all checkbox to the header
            $('#selectAllCheckbox').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });
        }
    });

    $('#typeFilter').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        table.columns(3).search(selectedType).draw();
    });

    $('#stautsFilter').on('change', function() {
        var selectedStatus = $(this).val().toLowerCase();
        table.columns(4).search(selectedStatus).draw();
    });
    <?php endif ?>

    datatableRows = table.rows({
        'search': 'applied'
    }).nodes();

    <?php if ($navActive == 'shared' && $shareType == 'wh'): ?>
    var nameColumns = $('td[data-label="name"] span', datatableRows)
    nameColumns.each((i, element) => {
        var option = $(`<option value="${element.textContent.trim()}">${element.textContent}</option>`)
        $('#userByFilter').append(option)
    })
    <?php endif ?>

    // Individual checkbox functionality
    $('.item-checkbox').change(function() {
        var checked = $('.item-checkbox:checked').length === $('.item-checkbox').length;
        $('#selectAllCheckbox').prop('checked', checked);
    });

    const resetModal = document.getElementById('resetConfirm');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', event => {
            resetModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = resetModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    const deleteModal = document.getElementById('deleteConfirm');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            deleteModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = deleteModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    const statusChange = document.querySelectorAll('.change-status');
    if (statusChange) {
        statusChange.forEach((element, i) => {
            element.addEventListener('click', event => {
                const statusBox = document.createElement('select');
                statusBox.classList.add('status', 'form-control');
                const currentStatus = event.target.getAttribute('data-status');

                const statusOptions = {
                    '<?= TOURNAMENT_STATUS_INPROGRESS ?>': 'In progress',
                    '<?= TOURNAMENT_STATUS_COMPLETED ?>': 'Completed',
                    '<?= TOURNAMENT_STATUS_ABANDONED ?>': 'Abandoned'
                }
                for (const [key, value] of Object.entries(statusOptions)) {
                    let el = document.createElement("option");
                    el.textContent = value;
                    el.value = key;
                    if (key == currentStatus) {
                        el.selected = true;
                    }
                    statusBox.appendChild(el);
                }

                $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find(
                    'td[data-label="status"]').html(statusBox);
                $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.btn-groups')
                    .addClass('visually-hidden');
                $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.save')
                    .removeClass('visually-hidden');
            })
        })
    }

    const shareModal = document.getElementById('shareModal');
    if (shareModal) {
        shareModal.addEventListener('show.bs.modal', event => {
            const base_url = "<?= base_url('tournaments/shared/') ?>";
            const tournament_id = event.relatedTarget.getAttribute('data-id');
            shareModal.setAttribute('data-id', tournament_id);

            const modalTitle = shareModal.querySelector('.modal-header .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');

            document.getElementById('shareHistoryModal').querySelector('.tournament-name').textContent = event.relatedTarget.getAttribute('data-name');
            document.getElementById('shareHistoryModal').querySelector('.close-share-history').dataset.name = event.relatedTarget.getAttribute('data-name');

            if (tournament_id) {
                fetchShareSettings(tournament_id);
            }

            $('#shareHistoryModal .close-share-history').attr('data-id', tournament_id);

            if (document.getElementById('share-users').checked) {
                $('.private-users').show();
                $('#userTagsInput').attr('disabled', false)
            } else {
                $('.private-users').hide();
                $('#userTagsInput').attr('disabled', true)
            }

            $('#userTagsInput').tagsinput('removeAll');

            if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_VIEW?>") {
                shareModal.querySelector('#sharePermissionHelpBlock').textContent = "User(s) can view the tournament brackets."
            }

            if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_EDIT?>") {
                shareModal.querySelector('#sharePermissionHelpBlock').innerHTML = 'User(s) can view and execute actions on the tournament brackets. <br/> Note that actions are logged for tracking purposes in the "View Log" feature of the tournament.'
            }

            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(
                popoverTriggerEl))

            $('input[name="usertype"]').on('change', event => {
                shareModal.querySelectorAll('.selected-target')[0].textContent = event.delegateTarget.dataset.target;

                if (document.getElementById('share-users').checked) {
                    $('.private-users').show();
                    $('#userTagsInput').attr('disabled', false)
                } else {
                    $('.private-users').hide();
                    $('#userTagsInput').attr('disabled', true)
                }
            })

            $('select[name="permission"]').on('change', event => {
                if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_VIEW?>") {
                    shareModal.querySelector('#sharePermissionHelpBlock').textContent = "User(s) can view the tournament brackets."
                }

                if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_EDIT?>") {
                    shareModal.querySelector('#sharePermissionHelpBlock').innerHTML = 'User(s) can view and execute actions on the tournament brackets. <br/> Note that actions are logged for tracking purposes in the "View Log" feature of the tournament.'
                }
            })

            $('#privateUserTagsInputForm')
                .find('[name="private-users"]')
                // Revalidate the cities field when it is changed
                .change(function(e) {
                    console.log($(e.target).val())
                    $('#privateUserTagsInputForm').bootstrapValidator('revalidateField', 'private-users');
                }).end()
                .bootstrapValidator({
                    framework: 'bootstrap',
                    excluded: ':disabled',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        "private-users": {
                            validators: {
                                notEmpty: {
                                    message: 'Please select at least one user.'
                                }
                            }
                        },
                    }
                });
        })

    }

    const shareHistoryModal = document.getElementById('shareHistoryModal');
    if (shareHistoryModal) {
        shareHistoryModal.addEventListener('show.bs.modal', event => {

            document.getElementById('shareHistoryModal').querySelectorAll('td .path').forEach((ele, i) => {
                const tooltip = bootstrap.Tooltip.getOrCreateInstance(ele)
            })
        })

    }

    const viewLogModal = document.getElementById('viewLogModal');
    if (viewLogModal) {
        viewLogModal.addEventListener('show.bs.modal', event => {
            viewLogModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));

            drawActionHistoryTable(event.relatedTarget.getAttribute('data-id'));
        })
    }

    const myCollapsible = $('.collapse', datatableRows)
    myCollapsible.each((i, item) => {
        item.addEventListener('hide.bs.collapse', event => {
            item.previousElementSibling.innerHTML = `<i class="fa-solid fa-plus"></i> View Actions`
        })
        item.addEventListener('show.bs.collapse', event => {
            myCollapsible.each((ii, e) => {
                if (ii != i) {
                    e.previousElementSibling.innerHTML = `<i class="fa-solid fa-plus"></i> View Actions`
                }
                $(e).collapse('hide')
            })

            item.previousElementSibling.innerHTML = `<i class="fa-solid fa-minus"></i> Hide Actions`
        })
    })

    const bulkActionConfirmModal = document.getElementById('bulkActionConfirmModal');
    if (bulkActionConfirmModal) {
        bulkActionConfirmModal.addEventListener('show.bs.modal', event => {
            var action = event.relatedTarget.actionname; // Action defined in data-action attribute
            let title = '';
            let action_text = '';
            var modal = $(this);
            modal.find('.modal-body .input-wrapper').empty();

            if (action === 'bulkDelete') {
                title = "Confirm to delete"
                action_text = '<h5>You are about to delete the following selected tournament(s):</h5>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
            } else if (action === 'bulkReset') {
                title = "Confirm To reset"
                action_text = '<h5>You are about to reset the following selected tournament(s):</h5>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
            } else if (action === 'bulkStatusUpdate') {
                title = "Confirm to update the status"
                action_text = '<h6>You are about to change the status of the following selected tournament(s):</h6>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
            }

            modal.find('.modal-title').text(title);
            modal.find('.modal-body .confirm-msg').html(action_text);

            // Update confirm button action based on action
            var confirmButton = modal.find('#confirmBulkActionBtn');
            confirmButton.off('click'); // Remove any existing click handlers
            if (action === 'bulkDelete') {
                confirmButton.on('click', bulkDelete)
            } else if (action === 'bulkReset') {
                confirmButton.on('click', bulkReset)
            } else if (action === 'bulkStatusUpdate') {
                let status = $('.status-to').val()

                // Create label element
                var label = $('<label class="col-form-label col-auto justify-content-end">Status:</label>');
                // Create select box element
                var selectBox = $('<select class="form-control" id="statusUpdateTo">');
                <?php if ($navActive == 'all'): ?>
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Complete</option>');
                <?php elseif ($navActive == 'archived'): ?>
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In Progress</option>');
                <?php endif ?>
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandone</option>');

                var selectBoxWrapper = $('<div class="col-auto"></div>')
                selectBoxWrapper.append(selectBox)

                var row = $('<div class="row"></div>')
                row.append(label).append(selectBoxWrapper)

                // Append select box to modal body
                modal.find('.modal-body .input-wrapper').append(row);
                // confirmButton.on('click', bulkStatusUpdate(status));
                confirmButton.on('click', bulkStatusUpdate)
            }
        })
    }

    $('#confirmDelete').on('click', function() {
        const tournament_id = deleteModal.getAttribute('data-id');

        $.ajax({
            type: "get",
            url: `${apiURL}/tournaments/${tournament_id}/delete`,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    $('.music-setting-link').on('click', function() {
        const tournament_id = $(this).data('id');

        $.ajax({
            type: "GET",
            url: apiURL + '/tournaments/' + tournament_id + '/music-settings',
            success: function(result) {
                result = JSON.parse(result);
                $('#music-settings-panel').html(result.html);
                $('#tournamentForm').data('id', tournament_id);

                if (result.data.length > 0) {
                    result.data.forEach((item, i) => {
                        let panel = $('.music-setting').eq(item.type);
                        panel.find("#toggle-music-settings-" + item.type).prop(
                            'checked', true);
                        panel.find('.setting').removeClass('visually-hidden');
                        panel.find('input[type="radio"][value="' + item.source +
                            '"]').prop('checked', true);

                        if (item.source == 'f') {
                            panel.find('input[data-source="file"]').attr('disabled', false);

                            if (item.path != '') {
                                panel.find('input[data-source="file"]').attr('required', false);
                            }

                            panel.find('input[name="file-path[' + item.type + ']"]').val(item.path);
                            panel.find('.playerSource').attr('src', '/uploads/' + item.path);
                            panel.find('.fileupload-hint').removeClass('d-none');
                            panel.find('.urlupload-hint').addClass('d-none');

                        }
                        if (item.source == 'y') {
                            panel.find('input[data-source="url"]').val(item.url).attr('disabled', false);
                            panel.find('.playerSource').attr('src', '/uploads/' + item.path);
                            panel.find('.fileupload-hint').addClass('d-none');
                            panel.find('.urlupload-hint').removeClass('d-none');
                        }

                        panel.find('.player').load();

                        panel.find('.preview input').attr('disabled', false);

                        let date = new Date(null);
                        date.setSeconds(item.start); // specify value for SECONDS here
                        panel.find('input.startAt[type="text"]').val(date.toISOString().slice(11, 19));
                        panel.find('input.startAt[type="hidden"]').val(item.start);

                        date = new Date(null);
                        date.setSeconds(item.end);
                        panel.find('input.stopAt').val(date.toISOString().slice(11, 19));
                        panel.find('input.stopAt[type="hidden"]').val(item.end);

                        panel.find('input.duration').val(item.duration);
                    });
                }

                $('#tournamentSettings').modal('show');
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    $('#submit').on('click', function() {
        const form = document.getElementById('tournamentForm');
        if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            form.classList.add('was-validated');
            return false;
        }

        let isValid = true;

        $('.music-setting').each((i, settingBox) => {
            const startTime0 = document.getElementsByName('start[' + i + ']')[0].value;
            const stopTime0 = document.getElementsByName('stop[' + i + ']')[0].value;

            if (parseInt(stopTime0) <= parseInt(startTime0)) {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.remove('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.remove('d-none');
                isValid = false;
            } else {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.add('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.add('d-none');
            }
        })

        if (!isValid) {
            return false;
        }

        const values = $('#tournamentForm').serializeArray();
        const data = Object.fromEntries(values.map(({
            name,
            value
        }) => [name, value]));

        $.ajax({
            url: apiURL + '/tournaments/' + $('#tournamentForm').data('id') + '/update-music',
            type: "POST",
            data: data,
            beforeSend: function() {
                //$("#preview").fadeOut();
                $("#err").fadeOut();
            },
            success: function(result) {
                var result = JSON.parse(result);
                if (result.error) {
                    // invalid file format.
                    $("#err").html("Invalid File !").fadeIn();
                } else {
                    $('#tournamentSettings').modal('hide');
                }
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    });

    $('#urlCopyBtn').on('click', function() {
        copyClipboard();
    });

    $('#confirmShare').on('click', function() {
        var validator = $("#privateUserTagsInputForm").data("bootstrapValidator");
        validator.validate();

        if (!validator.isValid()) {
            return;
        }

        const tournament_id = shareModal.dataset.id;
        const url = new URL($('#tournamentURL').val());
        var path = url.pathname.split("/");

        $.ajax({
            url: apiURL + '/tournaments/' + tournament_id + '/share',
            type: "POST",
            data: {
                'tournament_id': tournament_id,
                'target': $('input[name="usertype"]:checked').val(),
                'users': $('#userTagsInput').val(),
                'permission': $('select[name="permission"]').val(),
                'token': path[3]
            },
            beforeSend: function() {
                //$("#preview").fadeOut();
                $("#err").fadeOut();
            },
            success: function(result) {
                $('#shareModal').modal('hide');
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    });

    $('#confirmPurgeShare').on('click', function() {
        const shareSettingId = $('#purgeShareConfirm').attr('data-id');
        purgeShare(shareSettingId);
    })

    $('input[name="share-type"]').on('change', function(ele) {
        if ($(this).val() == 'wh') {
            window.location = "<?= base_url('tournaments?filter=shared&type=wh') ?>"
        } else {
            window.location = "<?= base_url('tournaments?filter=shared') ?>"
        }
    })

    const archiveModal = document.getElementById('archiveConfirmModal');
    if (archiveModal) {
        archiveModal.addEventListener('show.bs.modal', event => {
            archiveModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = archiveModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    $('#archiveConfirmBtn').on('click', function() {
        const tournament_id = archiveModal.getAttribute('data-id');
        let data = {
            'archive': 1
        }

        $.ajax({
            type: "post",
            url: `${apiURL}/tournaments/${tournament_id}/update`,
            data: data,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    const restoreModal = document.getElementById('restoreConfirmModal');
    if (restoreModal) {
        restoreModal.addEventListener('show.bs.modal', event => {
            restoreModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = restoreModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    $('#restoreConfirmBtn').on('click', function() {
        const tournament_id = restoreModal.getAttribute('data-id');
        let data = {
            'status': <?= TOURNAMENT_STATUS_INPROGRESS ?>
        }

        $.ajax({
            type: "post",
            url: `${apiURL}/tournaments/${tournament_id}/update`,
            data: data,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    var elt = $("#userTagsInput");
    elt.tagsinput({
        itemValue: "id",
        itemText: "username",
        typeaheadjs: {
            name: "task",
            displayKey: "username",
            source: task.ttAdapter()
        }
    });
});

const renameTorunament = (element) => {
    const nameBox = document.createElement('input');
    const name = $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('td a').eq(0).html();
    nameBox.classList.add('name', 'form-control');
    nameBox.value = name;

    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('td[data-label="name"]').html(nameBox);
    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('.btn-groups').addClass('visually-hidden');
    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('.save').removeClass('visually-hidden');
}

const cancelRenameTorunament = (element) => {
    const tournament_id = event.target.getAttribute('data-id');
    const name = $(event.target).parents('tr').find('.name').val();
    const nameElement = document.createElement('a');
    nameElement.href = '<?= base_url('tournaments') ?>/' + tournament_id + '/view';
    nameElement.textContent = name
    $(`tr[data-id="${tournament_id}"]`).find('td[data-label="name"]').html(nameElement);
    $(`tr[data-id="${tournament_id}"]`).find('.btn-groups').removeClass('visually-hidden');
    $(`tr[data-id="${tournament_id}"]`).find('.save').addClass('visually-hidden');
}

function saveChange() {
    let data = {};
    const tournament_id = event.target.getAttribute('data-id');
    if ($(event.target).parents('tr').find('.name').length > 0)
        data['name'] = $(event.target).parents('tr').find('.name').val();
    if ($(event.target).parents('tr').find('.status').length > 0)
        data['status'] = $(event.target).parents('tr').find('.status').val();

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/${tournament_id}/update`,
        data: data,
        success: function(result) {
            const data = JSON.parse(result).data;

            if (data.name != undefined && data.name != '') {
                const nameElement = document.createElement('a');
                nameElement.href = '<?= base_url('tournaments') ?>/' + tournament_id + '/view';
                nameElement.textContent = data.name
                $(`tr[data-id="${tournament_id}"]`).find('td[data-label="name"]').html(nameElement);
            }

            if (data.status != undefined && data.status != '') {
                let statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_INPROGRESS] ?>';
                if (data.status == '<?= TOURNAMENT_STATUS_COMPLETED ?>')
                    statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_COMPLETED] ?>';
                if (data.status == '<?= TOURNAMENT_STATUS_ABANDONED ?>')
                    statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_ABANDONED] ?>';

                $(`tr[data-id="${tournament_id}"]`).find('td[data-label="status"]').html(statusLabel);
            }


            $(`tr[data-id="${tournament_id}"]`).find('.btn-groups').removeClass('visually-hidden');
            $(`tr[data-id="${tournament_id}"]`).find('.save').addClass('visually-hidden');
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

function copyClipboard() {
    // Get the text field
    var copyText = document.getElementById("tournamentURL");

    // Select the text field
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    if (navigator.clipboard) {
        navigator.clipboard.writeText(copyText.value);
    } else {
        document.execCommand('copy');
    }
}

function drawActionHistoryTable(tournament_id) {
    $.ajax({
        type: "get",
        url: `${apiURL}/tournaments/${tournament_id}/getActionHistory`,
        success: function(result) {
            result = JSON.parse(result);
            let tbody = $('.action-history tbody');
            let rows = '<td colspan="4">History was not found.</td>';

            if (result.history) {
                rows = '';
                result.history.forEach((record, i) => {
                    if (!record.name) record.name = 'Guest'
                    rows += '<tr>';
                    rows += '<td>' + (i + 1) + '</td>';
                    rows += '<td>' + record.name + '</td>';
                    rows += '<td>' + record.action + '</td>';
                    rows += '<td>' + record.time + '</td>';
                    rows += '</tr>';
                })
            }

            tbody.html(rows);
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

function fetchShareSettings(tournament_id) {
    $.ajax({
        url: apiURL + '/tournaments/' + tournament_id + '/share',
        type: "GET",
        beforeSend: function() {
            //$("#preview").fadeOut();
            $("#err").fadeOut();
        },
        success: function(result) {
            result = JSON.parse(result);

            $('#shareModal #tournamentURL').val("<?= base_url('/tournaments/shared/') ?>" + result.token);
            if (result.settings.length) {
                let tbody = '';
                result.settings.forEach((item, i) => {
                    let permission = 'View';
                    if (item.permission == "<?= SHARE_PERMISSION_EDIT ?>") permission = 'Edit';

                    let target = 'Private';
                    if (item.target == "<?= SHARE_TO_EVERYONE ?>") target = 'Anyone';
                    if (item.target == "<?= SHARE_TO_PUBLIC ?>") target = 'Public';
                    if (item.target == "<?= SHARE_TO_USERS ?>") target += `<br/>Share with: ${item.private_users}`;

                    tbody += `<tr data-id="${item.id}" data-tournament-id="${item.tournament_id}">
                        <td>${i + 1}</td>
                        <td><span class="path" data-bs-toggle="tooltip" data-bs-title="<?= base_url('/tournaments/shared/') ?>${item.token}"><?= base_url('/tournaments/shared/') ?>${item.token}</span></td>
                        <td><span class="date">${item.created_at}</span></td>
                        <td><span class="date modified">${item.created_at == item.updated_at ? '' : item.updated_at}</span></td>
                        <td class="target">${target}</td>
                        <td class="permission">${permission}</td>
                        <td>${item.deleted_at ? 'Purged' : 'Active'}</td>
                        <td class="actions">
                            <div class="btns">
                                <a href="javascript:;" onClick="resetShare(this)">Reset</a><br/>
                                <a href="javascript:;" data-id="${item.id}" onclick="purgeShareConfirm(${item.id})">Purge</a>
                            </div>
                        </td>
                    </tr>`;
                });

                $('table.share-settings tbody').html(tbody);
                $('.close-share-history').data('id', tournament_id);

            } else {
                $('table.share-settings tbody').html(
                    '<tr><td colspan="8">No share settings found.</td></tr>');
            }
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function purgeShareConfirm(item_id) {
    $('#purgeShareConfirm').attr('data-id', item_id);
    $('#purgeShareConfirm').modal('show')
}

function purgeShare(id) {
    $.ajax({
        type: "GET",
        url: `${apiURL}/tournaments/purge-share/${id}`,
        success: function(result) {
            result = JSON.parse(result);

            $(`tr[data-id="${id}"]`).remove();

            if (!result.shares || result.shares.length < 1) {
                if ($('table.shared-by-me').length) {
                    $(`table.shared-by-me tr[data-id="${result.tournament_id}"]`).remove();
                    $('#shareHistoryModal').modal('hide');
                }
            }

            $('#purgeShareConfirm').modal('hide');
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

function resetShare(ele) {
    let row = $(ele).parents('tr');
    let url = row.find('span.path').html();
    const id = row.data('id');
    const tournament_id = row.data('tournament-id');

    $.ajax({
        type: "GET",
        url: `${apiURL}/tournaments/fetchShareSetting/${id}`,
        success: function(result) {
            const share = JSON.parse(result).share;

            const table = row.parents('table')
            row.find('td.actions .btns').hide()
            table.find('tr.editable').remove()

            let targetHtml = `<form id="privateUserUpdateForm" class="row">
                <div class="col-md-4 col-sm-4">
                    <select class="target form-select" aria-label="Default select example" onchange="changeShareUpdate(this)">
                        <option value="<?= SHARE_TO_PUBLIC ?>" ${share.target == "<?= SHARE_TO_PUBLIC ?>" ? "selected" : ""}>Public</option>
                        <option value="<?= SHARE_TO_EVERYONE ?>" ${share.target == "<?= SHARE_TO_EVERYONE ?>" ? "selected" : ""}>Everyone</option>
                        <option value="<?= SHARE_TO_USERS ?>" ${share.target == "<?= SHARE_TO_USERS ?>" ? "selected" : ""}>Private</option>
                    </select>
                </div>
                <div class="shareEditUsersWrapper col-md-8 col-sm-8" ${share.target == "<?= SHARE_TO_USERS ?>" ? "" : 'style="display: none"'}><input type="text" id="userTagsInputUpdate" name="private-users" class="form-control" placeholder="Enter registered username(s)" required /></div>
                </form>`;

            let permissionHtml = `<select class="permission form-select" aria-label="Default select example" onchange="changeShareUpdate(this)">
                <option value="<?= SHARE_PERMISSION_VIEW ?>" ${share.permission == "<?= SHARE_PERMISSION_VIEW ?>" ? "selected" : ""}>View</option>
                <option value="<?= SHARE_PERMISSION_EDIT ?>" ${share.permission == "<?= SHARE_PERMISSION_EDIT ?>" ? "selected" : ""}>Edit</option>
                </select>`;

            let html = `<tr class="editable" data-id="${id}" data-tournament-id="${tournament_id}">
                <td></td>
                <td><span class="path" data-bs-toggle="tooltip" data-bs-title="<?= base_url('/tournaments/shared/') ?>${share.token}"><?= base_url('/tournaments/shared/') ?>${share.token}</span></td>
                <td colspan="3">${targetHtml}</td>
                <td>${permissionHtml}</td>
                <td colspan="2"><a href="javascript:;" onclick="updateShareSetting(this)">Save</a> <a href="javascript:;" onclick="cancelUpdateSharing(this)">Cancel</a></td>
                </tr>`

            $(html).insertAfter(row);


            $("#userTagsInputUpdate").tagsinput({
                itemValue: "id",
                itemText: "username",
                typeaheadjs: {
                    name: "task",
                    displayKey: "username",
                    source: task.ttAdapter()
                }
            });

            if (share.target == "<?= SHARE_TO_USERS ?>") {
                share.private_users.forEach((user) => {
                    $("#userTagsInputUpdate").tagsinput('add', {
                        id: user.id,
                        username: user.username
                    });
                })
            }

            $('#privateUserUpdateForm')
                .find('[name="private-users"]')
                // Revalidate the cities field when it is changed
                .change(function(e) {
                    $('#privateUserUpdateForm').bootstrapValidator('revalidateField', 'private-users');
                }).end()
                .bootstrapValidator({
                    framework: 'bootstrap',
                    excluded: ':disabled',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        "private-users": {
                            validators: {
                                notEmpty: {
                                    message: 'Please select at least one user.'
                                }
                            }
                        },
                    }
                });
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

function changeShareUpdate(ele) {
    let row = $(ele).parents('tr')
    if (row.find('select.target').val() == "<?= SHARE_TO_USERS ?>") {
        $('.shareEditUsersWrapper').show()
        $('#userTagsInputUpdate').attr('disabled', false)
    } else {
        $('.shareEditUsersWrapper').hide()
        $('#userTagsInputUpdate').attr('disabled', true)
    }
}

function cancelUpdateSharing(ele) {
    const row = $(ele).parents('tr');
    const id = row.data('id')
    row.parents('table').find('tr[data-id="' + id + '"] .actions .btns').show()
    row.remove()
}

function updateShareSetting(ele) {
    var validator = $("#privateUserUpdateForm").data("bootstrapValidator");
    validator.validate();

    if (!validator.isValid()) {
        return;
    }

    let row = $(ele).parents('tr');
    const tournament_id = row.data('tournament-id');
    const share_id = row.data('id');
    const url = new URL(row.find('span.path').html());
    var path = url.pathname.split("/");

    $.ajax({
        url: apiURL + '/tournaments/' + tournament_id + '/share',
        type: "POST",
        data: {
            'tournament_id': tournament_id,
            'target': row.find('td select.target').val(),
            'permission': row.find('td select.permission').val(),
            'token': path[3],
            'users': $('#userTagsInputUpdate').val()
        },
        beforeSend: function() {
            //$("#preview").fadeOut();
            $("#err").fadeOut();
        },
        success: function(result) {
            result = JSON.parse(result);
            row = row.parents('tbody').find('tr[data-id="' + result.share.id + '"]').first()

            if (result.share) {
                let permission = 'View';
                if (result.share.permission == "<?= SHARE_PERMISSION_EDIT ?>") permission = 'Edit';

                let targetHtml = 'Private';
                if (result.share.target == "<?= SHARE_TO_PUBLIC ?>") targetHtml = 'Public';
                if (result.share.target == "<?= SHARE_TO_EVERYONE ?>") targetHtml = 'Everyone';
                if (result.share.target == "<?= SHARE_TO_USERS ?>") targetHtml += `<br/>Share with: ${result.share.private_users}`;
                row.find('td.target').html(targetHtml);

                let permissionHtml = '';
                if (result.share.permission == "<?= SHARE_PERMISSION_VIEW ?>") permissionHtml = 'View';
                if (result.share.permission == "<?= SHARE_PERMISSION_EDIT ?>") permissionHtml = 'Edit';
                row.find('td.permission').html(permissionHtml);

                row.find('td span.modified').html(result.share.updated_at);
            }

            cancelUpdateSharing(ele)
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function generateURL() {
    var url = $('#tournamentURL').val();
    url = new URL(url);
    var path = url.pathname.split("/");
    var token = '';
    for (var i = 0; i < path[3].length; i++) {
        var randomIndex = Math.floor(Math.random() * path[3].length);
        token += path[3].charAt(randomIndex);
    }

    $('#shareModal #tournamentURL').val("<?= base_url('/tournaments/shared/') ?>" + token);
}

function handleKeyPress(event) {
    if (event.keyCode === 13) {
        event.preventDefault(); // Prevent form submission
        fetchDataAndUpdateTable();
    }
}

function fetchDataAndUpdateTable() {
    let data = {
        query: $('#tournamentSearchInputBox').val()
    }

    let url = new URL(window.location.href);

    // Get search params from URL
    let searchParams = new URLSearchParams(url.search);

    // Add new parameter
    searchParams.set('query', $('#tournamentSearchInputBox').val());

    // Update search property of URL object
    url.search = searchParams.toString();

    // Replace current history state with new URL
    history.replaceState(null, '', url.href);

    window.location.href = url.href
}

function confirmBulkAction() {
    var selectedIds = [];
    var names = '';
    if ($('#selectAllCheckbox').is(":checked")) {
        $(bulkActionConfirmModal).modal('show', {
            'actionname': $(event.currentTarget).data('actionname'),
            'names': "All Tournaments"
        })
    } else {
        $('.item-checkbox:checked').each(function(i, item) {
            selectedIds.push($(this).closest('tr').data('id'));
            names += $(this).closest('tr').find('td a').eq(0).html();

            if (i < ($('.item-checkbox:checked').length - 1)) {
                names += ', '
            }
        });

        if (selectedIds.length) {
            $(bulkActionConfirmModal).modal('show', {
                'actionname': $(event.currentTarget).data('actionname'),
                'names': names
            })
        } else {
            alert('Please select the tournaments.')
        }
    }

}

// handling bulk action (e.g., delete)
function bulkDelete() {
    var selectedIds = [];
    var rows = table.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    // Perform your bulk action (e.g., AJAX call to delete items)
    if (!selectedIds) {
        return false
    }

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkDelete`,
        data: {
            id: selectedIds
        },
        success: function(result) {
            result = JSON.parse(result)
            console.log(result)
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkReset() {
    var selectedIds = [];
    var rows = table.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkReset`,
        data: {
            id: selectedIds
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkStatusUpdate() {
    var selectedIds = [];
    var rows = table.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    // Perform your bulk action (e.g., AJAX call to delete items)
    if (!selectedIds) {
        return false
    }

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkUpdate`,
        data: {
            id: selectedIds,
            status: $('#statusUpdateTo').val()
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};
</script>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap-theme.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<style>
.resizable {
    position: relative;
}

.resizable:after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
}

.path {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    /* Adjust the width as needed */
    display: inline-block;
}
</style>

<?= $this->endSection() ?>