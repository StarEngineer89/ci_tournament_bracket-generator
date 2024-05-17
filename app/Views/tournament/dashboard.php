<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Dashboard<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="card col-12 shadow-sm">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-center"><?//= lang('Auth.login') ?>Tournament Dashboard</h5>
            <div class="buttons d-flex justify-content-end">
            <a class="btn btn-success" href="<?php echo base_url('/tournaments/create') ?>"><i class="fa-sharp fa-solid fa-plus"></i> Create</a>
            </div>

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

            <table class="table align-middle">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">Tournament Name</th>
                <th scope="col">Type</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tournaments as $index => $tournament): ?>
            <tr data-id="<?= $tournament['id'] ?>">
                <th scope="row"><?= $index + 1 ?></th>
                <td>
                    <a href="<?= base_url('tournaments/' . $tournament['id'] . '/view') ?>"><?= $tournament['name'] ?></a>
                </td>
                <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
                <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
                <td>
                    <div class="btn-groups list-group">
                        <a href="javascript:;" class="rename" data-id="<?= $tournament['id'] ?>">Rename</a>
                        <a href="javascript:;" class="reset" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#resetConfirm">Reset</a>
                        <a href="javascript:;" class="delete" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#deleteConfirm">Delete</a>
                        <a href="javascript:;" class="change-status" data-id="<?= $tournament['id'] ?>" data-status="<?= $tournament['status'] ?>">Change Status</a>
                        <a href="javascript:;" class="music-setting-link" data-id="<?= $tournament['id'] ?>">Music Settings</a>
                    </div>
                    
                    <a href="javascript:;" class="save visually-hidden" data-id="<?= $tournament['id'] ?>" data-status="<?= $tournament['status'] ?>" onClick="saveChange(event)">Save</a>
                </td>
            </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
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
                    <h5>Are you sure you want to delete this tournament "<span class="tournament-name"></span>"?</h1>
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
                    <h5>Are you sure you want to reset this tournament "<span class="tournament-name"></span>"?</h1>
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
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="/js/participants.js"></script>
    <script type="text/javascript">
        let apiURL = "<?= base_url('api')?>";

        $(document).ready(function() {
            $('#confirmReset').on('click', function() {
                const tournament_id = resetModal.getAttribute('data-id');
                $.ajax({
                    type: "GET",
                    url: apiURL + '/tournaments/' +  tournament_id + '/clear',
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
                    setTimeout(function(){
                        $("#overlay").fadeOut(300);
                    },500);
                });
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

            const renameBtn = document.querySelectorAll('.rename');
            if (renameBtn) {
                renameBtn.forEach((element, i) => {
                    element.addEventListener('click', event => {
                        const nameBox = document.createElement('input');
                        const name = $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('td a').eq(0).html();
                        nameBox.classList.add('name', 'form-control');
                        nameBox.value = name;

                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('td').eq(0).html(nameBox);
                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.btn-groups').addClass('visually-hidden');
                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.save').removeClass('visually-hidden');
                    })
                }) 
            }

            const statusChange = document.querySelectorAll('.change-status');
            if (statusChange) {
                statusChange.forEach((element, i) => {
                    element.addEventListener('click', event => {
                        const statusBox = document.createElement('select');
                        statusBox.classList.add('status', 'form-control');
                        const currentStatus = event.target.getAttribute('data-status');

                        const statusOptions = {'<?= TOURNAMENT_STATUS_INPROGRESS ?>': 'In progress', '<?= TOURNAMENT_STATUS_COMPLETED ?>': 'Completed', '<?= TOURNAMENT_STATUS_ABANDONED ?>': 'Abandoned'}
                        for (const [key, value] of Object.entries(statusOptions)) {
                            let el = document.createElement("option");
                            el.textContent = value;
                            el.value = key;
                            if (key == currentStatus) {
                                el.selected = true;
                            }
                            statusBox.appendChild(el);
                        }

                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('td[data-label="status"]').html(statusBox);
                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.btn-groups').addClass('visually-hidden');
                        $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.save').removeClass('visually-hidden');
                    })
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
                        window.location.href="/tournaments";
                    },
                    error: function(error) {
                        console.log(error);
                    }
                }).done(() => {
                    setTimeout(function(){
                        $("#overlay").fadeOut(300);
                    },500);
                });
            });

            $('.music-setting-link').on('click', function() {
                const tournament_id = $(this).data('id');

                $.ajax({
                    type: "GET",
                    url: apiURL + '/tournaments/' +  tournament_id + '/music-settings',
                    success: function(result) {
                        result = JSON.parse(result);
                        $('#music-settings-panel').html(result.html);
                        $('#tournamentForm').data('id', tournament_id);

                        if (result.data.length > 0) {
                            result.data.forEach((item, i) => {
                                let panel = $('.music-setting').eq(item.type);
                                panel.find("#toggle-music-settings-" + item.type).prop('checked', true);
                                panel.find('.setting').removeClass('visually-hidden');
                                panel.find('input[type="radio"][value="' + item.source + '"]').prop('checked', true);

                                if (item.source == 'f') {
                                    panel.find('input[data-source="file"]').attr('disabled', false);
                                    
                                    if (item.path != '') {
                                        panel.find('input[data-source="file"]').attr('required', false);
                                    }

                                    panel.find('input[name="file-path['+item.type+']"]').val(item.path);
                                    panel.find('.playerSource').attr('src', '/uploads/' + item.path);

                                }
                                if (item.source == 'y') {
                                    panel.find('input[data-source="url"]').val(item.path).attr('disabled', false);
                                    panel.find('.playerSource').attr('src', item.path);
                                }
                                
                                panel.find('.player').load();

                                panel.find('.preview input').attr('disabled', false);
                                
                                const date = new Date(null);
                                date.setSeconds(item.start); // specify value for SECONDS here
                                panel.find('input.startAt[type="text"]').val(date.toISOString().slice(11, 19));
                                panel.find('input.startAt[type="hidden"]').val(item.start);
                                
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
                    setTimeout(function(){
                        $("#overlay").fadeOut(300);
                    },500);
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

                const values = $('#tournamentForm').serializeArray();
                const data = Object.fromEntries(values.map(({name, value}) => [name, value]));
                
                $.ajax({
                    url: apiURL + '/tournaments/' + $('#tournamentForm').data('id') + '/update-music',
                    type: "POST",
                    data:  data,
                    beforeSend : function()
                    {
                        //$("#preview").fadeOut();
                        $("#err").fadeOut();
                    },
                    success: function(result)
                    {
                        var result = JSON.parse(result);
                        if(result.error)
                        {
                            // invalid file format.
                            $("#err").html("Invalid File !").fadeIn();
                        }
                        else
                        {
                            $('#tournamentSettings').modal('hide');
                        }
                    },
                    error: function(e) 
                    {
                        $("#err").html(e).fadeIn();
                    }          
                });
            });

        });
        
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
                        $(`tr[data-id="${tournament_id}"]`).find('td').eq(0).html(nameElement);
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
                setTimeout(function(){
                    $("#overlay").fadeOut(300);
                },500);
            });
        }
    </script>
<?= $this->endSection() ?>
