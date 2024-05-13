<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Dashboard<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="card col-12 shadow-sm">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-center"><?//= lang('Auth.login') ?>Tournament Dashboard</h5>
            <div class="buttons d-flex justify-content-end">
            <a class="btn btn-primary" href="<?php echo base_url('/tournaments/create') ?>">Create</a>
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
            <tr>
                <th scope="row"><?= $index + 1 ?></th>
                <td>
                    <a class="name" href="<?= base_url('tournaments/' . $tournament['id'] . '/view') ?>"><?= $tournament['name'] ?></a>
                </td>
                <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
                <td><?= ($tournament['status'] == 1) ? "In progress" : "Completed" ?></td>
                <td>
                    <div class="list-group">
                        <a href="javascript:;" class="rename" data-id="<?= $tournament['id'] ?>">Rename</a>
                        <a href="javascript:;" class="reset" data-id="<?= $tournament['id'] ?>">Reset</a>
                        <a href="javascript:;" class="music-setting-link" data-id="<?= $tournament['id'] ?>">Music Settings</a>
                    </div>
                </td>
            </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
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
            $('.rename').on('click', function() {
                const nameElement = $(this).parents('tr').find('.name');
                var opts = prompt('Tournament Name:', nameElement.html());

                if(!_.isNaN(opts)) {
                    $("#overlay").fadeIn(300);
                } else
                    alert('Please input the name of the participant.');
                $.ajax({
                    type: "POST",
                    url: apiURL + '/tournaments/' +  $(this).data('id') + '/update',
                    data: {'name': opts},
                    success: function(result) {
                        const data = JSON.parse(result).data;
                        nameElement.html(data.name);
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

            $('.reset').on('click', function() {
                $.ajax({
                    type: "GET",
                    url: apiURL + '/tournaments/' +  $(this).data('id') + '/clear',
                    success: function(result) {
                        alert("Brackets was cleared successfully.");
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
                                panel.find('input.duration[type="text"]').attr('disabled', true);

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
    </script>
<?= $this->endSection() ?>
