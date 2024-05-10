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
                        <!-- Music during the shuffling -->
                        <h6 class="border-bottom"-1>Music during generating brackets</h6>
                        <div class="music-setting p-2 mb-1">
                            <input type="hidden" name="audioType[0]" value="0">
                            <div class="input-group mb-3">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[0]" data-target="file" checked>
                                </div>
                                <input type="file" class="form-control music-source" data-source="file" name="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi">
                                <label class="input-group-text" for="file-input">Upload</label>
                                <input type="hidden" name="file-path[0]">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[0]" data-target="url">
                                </div>
                                <span class="input-group-text">https://www.youtube.com/</span>
                                <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]">
                            </div>
                            <div class="mb-3 preview">
                                <audio controls class="w-100 player">
                                    <source class="playerSource" src="" type="audio/mpeg" />
                                </audio>

                                <div class="row row-cols-lg-auto row-cols-md-auto g-3 align-items-center">
                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Start</div>
                                            <input type="text" class="form-control form-control-sm startAt" name="start[0]">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Stop</div>
                                            <input type="text" class="form-control form-control-sm stopAt" name="stop[0]">
                                        </div>

                                    </div>
                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Duration</div>
                                            <input type="text" class="form-control form-control-sm duration" name="duration[0]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Music for the Final Winner -->
                        <h6 class="border-bottom"-1>Music for a Final Winner</h6>
                        <div class="music-setting p-2 mb-1">
                            <input type="hidden" name="audioType[1]" value="1">

                            <div class="input-group mb-3">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[1]" data-target="file" checked>
                                </div>
                                <input type="file" class="form-control music-source" data-source="file" name="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi">
                                <label class="input-group-text" for="file-input">Upload</label>
                                <input type="hidden" name="file-path[1]">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[1]" data-target="url">
                                </div>
                                <span class="input-group-text">https://www.youtube.com/</span>
                                <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]">
                            </div>
                            <div class="mb-3 preview">
                                <audio controls class="w-100 player">
                                    <source class="playerSource" src="" type="audio/mpeg" />
                                </audio>

                                <div class="row row-cols-lg-auto row-cols-md-auto g-3 align-items-center">
                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Start</div>
                                            <input type="text" class="form-control form-control-sm startAt" name="start[1]">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Stop</div>
                                            <input type="text" class="form-control form-control-sm stopAt" name="stop[1]">
                                        </div>

                                    </div>
                                    <div class="col-4">
                                        <div class="input-group">
                                            <div class="input-group-text">Duration</div>
                                            <input type="text" class="form-control form-control-sm duration" name="duration[1]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                $('#tournamentSettings').modal('show');
                $('#tournamentForm').data('id', tournament_id);
                $('#tournamentForm input[type="text"]').val('');

                $.ajax({
                    type: "GET",
                    url: apiURL + '/tournaments/' +  tournament_id + '/music-settings',
                    success: function(result) {
                        result = JSON.parse(result);

                        if (result.data.length > 0) {
                            result.data.forEach((item, i) => {
                                let panel = $('.music-setting').eq(i);
                                panel.find('input[type="radio"][value="' + item.source + '"]').prop('checked', true);
                                panel.find('input.startAt').val(item.start);
                                panel.find('input.stopAt').val(item.end);
                                panel.find('input.duration').val(item.duration);
                                
                                if (item.source == 'f') {
                                    panel.find('input[name="file-path['+i+']"]').val(item.path);
                                    panel.find('.playerSource').attr('src', '/uploads/' + item.path);
                                    panel.find('.player').load();
                                } else {
                                    panel.find('input.music-source[type="text"]').val(item.path);
                                }   
                            });
                        }
                        console.log(result);
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
                if (!$('#tournamentForm').valid()) {
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
