<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="/js/participants.js"></script>
<script type="text/javascript">
    let apiURL = "<?= base_url('api')?>";
    let eleminationType;
    let tournament_id;
    
    const itemList = document.getElementById('newList');

    $(window).on('load', function() {
        $("#preview").fadeIn();
    });
    $(document).ready(function() {
        $('#toggle-music-settings').on('change', function() {
            if ($(this).prop( "checked") == true) {
                $('#music-settings-panel').find('input').attr('required', true);
                $('#music-settings-panel').removeClass('invisible');
            } else {
                $('#music-settings-panel').find('input').attr('required', false);
                $('#music-settings-panel').addClass('invisible');
            }
        });

        $('#tournamentSettings input[type="radio"]').on('change', function() {
            $('.music-source').attr('disabled', true);
            if ($(this).data('target') == 'file')
                $('#file-input').attr('disabled', false);
            if ($(this).data('target') == 'url')
                $('#music-url').attr('disabled', false);
        });

        $('#startAt, #stopAt').on('change', function() {
            const starttime = $('#startAt').val();
            const stoptime = $('#stopAt').val();

            if (starttime !== 'undefined' && stoptime !== 'undefined' && starttime !== '' && stoptime !== '') {
                $('#duration').val(stoptime - starttime);
            }
        });

        $('#duration').on('change', function() {
            const starttime = $('#startAt').val();
            const duration = $('#duration').val();

            if (starttime !== 'undefined' && duration !== 'undefined' && starttime !== '' && duration !== '') {
                $('#stopAt').val(parseInt(starttime) + parseInt(duration));
            }
        });

        $('#file-input').on('change', function(e) {
            e.preventDefault();
            var formData = new FormData();
            formData.append('audio', $('#file-input')[0].files[0]);
            $.ajax({
                url: apiURL + '/tournaments/upload',
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                beforeSend : function()
                {
                    //$("#preview").fadeOut();
                    $("#err").fadeOut();
                },
                success: function(data)
                {
                    var data = JSON.parse(data);
                    if(data.error)
                    {
                        // invalid file format.
                        $("#err").html("Invalid File !").fadeIn();
                    }
                    else
                    {
                        $('#filePath').val(data.path);
                        $('#playerSource').attr('src', '<?= base_url('uploads/') ?>' + data.path);
                        $('#player').load();
                        // view uploaded file.
                        $("#preview").fadeIn();
                    }
                },
                error: function(e) 
                {
                    $("#err").html(e).fadeIn();
                }          
            });
        });

        $('#submit').on('click', function() {
            if (!$('#tournamentForm').valid()) {
                return false;
            }

            const values = $('#tournamentForm').serializeArray();
            const data = Object.fromEntries(values.map(({name, value}) => [name, value]));
            
            $.ajax({
                url: apiURL + '/tournaments/save',
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
                        const eleminationType = (result.data.eliminationType == 1) ? "Single" : "Double";
                        const audioSrc = (result.data.path == 1) ? '<?= base_url('uploads/') ?>' + result.data.path : 'https://www.youtube.com/' + result.data.path;
                        const tournament_id = result.data.tournament_id;

                        let audio = document.getElementById("myAudio");
                        $('#shuffleMusic').attr('src', audioSrc);
                        audio.play();
                        callShuffle();
                    }
                },
                error: function(e) 
                {
                    $("#err").html(e).fadeIn();
                }          
            });
        });

        $('#generate').on('click', function() {
            $('#tournamentSettings').modal('show');
        });

        $('#add-participant').on('click', function() {
            var opts = prompt('Participant Name:', 'Guild');
            
            if(!_.isNaN(opts)) {
                $("#overlay").fadeIn(300);

                $.ajax({
                    type: "POST",
                    url: apiURL + '/participants/new',
                    data: { 'name': opts },
                    dataType: "JSON",
                    success: function(result) {
                        var participants = result.participant;
                        renderParticipants(participants);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                }).done(() => {
                    setTimeout(function(){
                        $("#overlay").fadeOut(300);
                    },500);
                });
            } else
                alert('Please input the name of the participant.');
        });
        
        $('#clear').on('click', function() {
            $.ajax({
                type: "GET",
                url: apiURL + '/brackets/clear',
                success: function(result) {
                    alert("Brackets was cleared successfully.");

                    window.location.href = '/';
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
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

        <div class="card col-12 shadow-sm">
            <div class="card-body">
                <h5 class="card-title d-flex justify-content-center"><?//= lang('Auth.login') ?>Tournament Participants</h5>
                <div class="buttons d-flex justify-content-center">
                    <button id="add-participant" class="btn btn-default">Add Participant</button>
                    <button id="generate" class="btn btn-default">Generate Elimination</button>
                    <button id="clear" class="btn btn-default">Reset (Clear)</button>
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

                <div id="newList" class="list-group"></div>
            </div>
        </div>

    <audio id="myAudio" style="display:none">
        <source src="" type="audio/mpeg" id="shuffleMusic">
    </audio>

    <!-- Modal -->
    <div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tournament Properties</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                <form id="tournamentForm" method="POST" endtype="multipart/form-data">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="title">Title</span>
                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="title" name="title" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="type">Elimination Type</span>
                        <select class="form-select" name="type" aria-label="type" required>
                            <option value="1" selected>Single</option>
                            <option value="2">Double</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="toggle-music-settings" name="setting-toggle">
                        <label class="form-check-label" for="toggle-music-settings">
                            Enable music settings
                        </label>
                    </div>
                    <div class="invisible" id="music-settings-panel">
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" value="file" aria-label="Radio button for following text input" name="source" data-target="file" checked>
                            </div>
                            <input type="file" class="form-control music-source" id="file-input" name="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi">
                            <label class="input-group-text" for="file-input">Upload</label>
                            <input type="hidden" name="file-path" id="filePath">
                        </div>
                        <div class="mb-3">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="youtube" aria-label="Radio button for following text input" name="source" data-target="url">
                                </div>
                                <span class="input-group-text" id="basic-addon3">https://www.youtube.com/</span>
                                <input type="text" class="form-control music-source" id="music-url" aria-describedby="basic-addon3 basic-addon4" name="url" disabled>
                            </div>
                        </div>
                        <div class="mb-3" id="preview">
                            <audio controls class="w-100" id="player">
                                <source id="playerSource" src="" type="audio/mpeg" />
                            </audio>

                            <div class="row row-cols-lg-auto row-cols-md-auto g-3 align-items-center">
                                <div class="col-4">
                                    <div class="input-group">
                                        <div class="input-group-text">Start</div>
                                        <input type="text" class="form-control form-control-sm" id="startAt" name="start">
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="input-group">
                                        <div class="input-group-text">Stop</div>
                                        <input type="text" class="form-control form-control-sm" id="stopAt" name="stop">
                                    </div>

                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <div class="input-group-text">Duration</div>
                                        <input type="text" class="form-control form-control-sm" id="duration" name="duration">
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
