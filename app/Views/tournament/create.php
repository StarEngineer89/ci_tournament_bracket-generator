<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="/js/participants.js"></script>
<script type="text/javascript">
    let apiURL = "<?= base_url('api')?>";
    let eleminationType;
    let tournament_id;
    let shuffle_duration = parseInt(<?= (isset($settings) && $settings) ? $settings[0]['duration'] : 10 ?>);
    
    const itemList = document.getElementById('newList');

    $(window).on('load', function() {
        $("#preview").fadeIn();
    });
    $(document).ready(function() {
        loadParticipants();

        $('#toggle-music-settings').on('change', function() {
            if ($(this).prop( "checked") == true) {
                $('#music-settings-panel').find('input').attr('required', true);
                $('#music-settings-panel').removeClass('invisible');
            } else {
                $('#music-settings-panel').find('input').attr('required', false);
                $('#music-settings-panel').addClass('invisible');
            }
        });

        $('#submit').on('click', function() {
            if (!$('#tournamentForm').valid()) {
                return false;
            }

            const values = $('#tournamentForm').serializeArray();
            const data = Object.fromEntries(values.map(({name, value}) => [name, value]));
            shuffle_duration = parseInt(data['duration[0]']);
            
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
                        tournament_id = result.data.tournament_id;
                        eleminationType = (result.data.type == 1) ? "Single" : "Double";
                        if (result.data.music !== undefined) {
                            shuffle_duration = parseInt(result.data.music[0].duration);
                        }
                        
                        if (result.data.music != undefined) {
                            let audioSrc = (result.data.music[0].source == 'f') ? '<?= base_url('uploads/') ?>' : 'https://www.youtube.com/';
                            audioSrc += result.data.music[0].path;

                            $('#audioSrc').attr('src', audioSrc);

                            let audio = document.getElementById("myAudio");
                            audio.play();
                        }
                        
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
            <?php if (isset($tournament) && count($tournament)): ?>
                tournament_id = "<?= $tournament['id'] ?>";
                eleminationType = "<?= ($tournament['type'] == 1) ? "Single" : "Double" ?>";
                
                <?php if (isset($settings) && count($settings)): ?>
                // let audio = $("#myAudio");
                // audio.prop("currentTime",12);
                let audio = document.getElementById("myAudio");
                audio.oncanplay = function() {
                    this.currentTime = 10;
                };
                // audio.currentTime = parseInt(<?= $settings[0]['start'] ?>);
                audio.play();
                <?php endif; ?>
                // audio.trigger('play');                
                callShuffle();
            <?php else: ?>
            $('#tournamentSettings').modal('show');
            <?php endif; ?>
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

    <audio id="myAudio" controls style="display:none" preload="auto">
        <?php if (isset($settings) && $settings): ?>
            <source src="<?= ($settings[0]['source'] == 'f') ? '/uploads//' . $settings[0]['path'] : 'https://www.youtube.com/' . $settings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
        <?php else: ?>
            <source src="" type="audio/mpeg" id="audioSrc">
        <?php endif; ?>
        
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
                                <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]" disabled>
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
                                <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]" disabled>
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
