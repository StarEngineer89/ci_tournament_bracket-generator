<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/js/participants.js"></script>
<!-- <script src="/js/player.js"></script> -->
<script type="text/javascript">
    let apiURL = "<?= base_url('api') ?>";
    let eleminationType;
    let tournament_id;
    let shuffle_duration = parseInt(<?= (isset($settings) && $settings) ? $settings[0]['duration'] : 10 ?>);
    let audio = document.getElementById("myAudio");
    let videoUrl = "https://youtu.be/Gb1iGDchKYs?si=fT3fFBreaYw_bh4l";
    let videoStartTime = 0;
    let videoDuration = 20;
    let duplicates = [];

    const itemList = document.getElementById('newList');

    $(window).on('load', function() {
        $("#preview").fadeIn();
    });
    $(document).ready(function() {
        loadParticipants();

        $('#submit').on('click', function(event) {
            const form = document.getElementById('tournamentForm');
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
                form.classList.add('was-validated');
                return false;
            }

            const values = $('#tournamentForm').serializeArray();
            const data = Object.fromEntries(values.map(({
                name,
                value
            }) => [name, value]));
            shuffle_duration = parseInt(data['duration[0]']);

            $.ajax({
                url: apiURL + '/tournaments/save',
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
                        tournament_id = result.data.tournament_id;
                        eleminationType = (result.data.type == 1) ? "Single" : "Double";
                        if (result.data.music !== undefined && result.data.music.type == 0) {
                            shuffle_duration = (result.data.music[0].duration) ? parseInt(result.data.music[0].duration) : 10;
                            let audioSrc = (result.data.music[0].source == 'f') ? '<?= base_url('uploads/') ?>' : '';
                            audioSrc += result.data.music[0].path;

                            $('#audioSrc').attr('src', audioSrc);
                            audio.load();
                        }

                        callShuffle();
                    }
                },
                error: function(e) {
                    $("#err").html(e).fadeIn();
                }
            });
        });

        $('#generate').on('click', function() {
            <?php if (isset($tournament) && count($tournament)) : ?>
                tournament_id = "<?= $tournament['id'] ?>";
                eleminationType = "<?= ($tournament['type'] == 1) ? "Single" : "Double" ?>";

                <?php if (isset($settings) && count($settings)) : ?>
                    audio.currentTime = parseInt(<?= $settings[0]['start'] ?>);
                <?php endif; ?>

                callShuffle();
            <?php else : ?>
                $('#tournamentSettings').modal('show');
            <?php endif; ?>
        });

        $('#addParticipants').on('click', function() {
            var opts = $('#participantNames').val();

            if (opts == '') {
                return false;
            }

            names = opts.replaceAll(', ', ',').split(',');

            if (names.length) {
                saveParticipants(names);
            }
        });

        $('#confirmSave .include').on('click', () => {
            $('#participantNames').val(null);
            $('input.csv-import').val(null)
            $('#confirmSave').modal('hide');
            $('#collapseAddParticipant').removeClass('show');
            appendAlert('Records inserted successfully!', 'success');
        })

        $('#confirmSave .remove').on('click', () => {
            if (duplicates.length) {
                removeDuplicates(duplicates);
            }

            $('#participantNames').val(null);
            $('input.csv-import').val(null)
            $('#confirmSave').modal('hide');
            $('#collapseAddParticipant').removeClass('show');
        })
    });

    var saveParticipants = (data) => {
        $.ajax({
            type: "POST",
            url: apiURL + '/participants/new',
            data: {
                'name': data
            },
            success: function(result) {
                result = JSON.parse(result);

                renderParticipants(result.participants);

                duplicates = result.duplicated;

                if (duplicates.length) {
                    let nameString = '';

                    duplicates.forEach((ele, i) => {
                        nameString += ele.name;

                        if (i < (duplicates.length - 1)) {
                            nameString += ', ';
                        }
                    })

                    $('#confirmSave .names').html(nameString);
                    $('#confirmSave').modal('show');

                    return false;
                }

                appendAlert('Records inserted successfully!', 'success');
                $("div.alert").fadeTo(5000, 500).slideUp(500, function() {
                    $("div.alert").slideUp(500);
                });
                
                $('#collapseAddParticipant').removeClass('show');
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

    var removeDuplicates = (data) => {
        $.ajax({
            type: "POST",
            url: apiURL + '/participants/removeDuplicates',
            data: {
                'names': data
            },
            success: function(result) {
                result = JSON.parse(result);

                appendAlert('Records inserted successfully!', 'success');
                $("div.alert").fadeTo(5000, 500).slideUp(500, function() {
                    $("div.alert").slideUp(500);
                });

                renderParticipants(result.participants);
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

    var csvUpload = (element) => {
        var formData = new FormData();
        formData.append('file', $('.csv-import')[0].files[0]);
        $.ajax({
            url: apiURL + '/participants/import',
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#err").fadeOut();
            },
            success: function(result) {
                result = JSON.parse(result);
                duplicates = result.duplicated;
                if (duplicates.length) {
                    let nameString = '';

                    duplicates.forEach((ele, i) => {
                        nameString += ele.name;

                        if (i < (duplicates.length - 1)) {
                            nameString += ', ';
                        }
                    })

                    $('#confirmSave .names').html(nameString);
                    $('#confirmSave').modal('show');
                }
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    }

    const appendAlert = (message, type) => {
        const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
        alertPlaceholder.innerHTML = ''
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        alertPlaceholder.append(wrapper)
    }
</script>

<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card container shadow-sm">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>

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

        <h5 class="card-title d-flex justify-content-center"><? //= lang('Auth.login') 
                                                                ?>
            Tournament Participants
        </h5>

        <div id="liveAlertPlaceholder"></div>

        <div class="participants-box m-auto">
            <div class="buttons d-flex justify-content-center">
                <button id="add-participant" class="btn btn-default" data-bs-toggle="collapse" data-bs-target="#collapseAddParticipant" aria-expanded="false" aria-controls="collapseAddParticipant">Add Participant</button>
                <button id="generate" class="btn btn-default">Generate Brackets</button>
            </div>
            <div class="collapse" id="collapseAddParticipant">
                <div class="card card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <textarea class="form-control form-control-lg" id="participantNames" placeholder="For example: name1,name2,name3"></textarea>
                            <button type="button" class="btn btn-primary mt-2 float-end" id="addParticipants">Save</button>
                        </div>
                    </form>
                    <div id="namesdHelpBlock" class="form-text">
                        Or, upload a csv file of participant names. <a href="<?= base_url('/uploads/sample.csv') ?>">Download sample template file</a>
                        <br />
                        Note that the first row header, as well as any other columns besides 1st column are ignored.
                    </div>

                    <form class="row row-cols-lg-auto g-3 align-items-center mt-1" enctype="multipart/form-data" method="post">
                        <div class="input-group mb-3">
                            <input type="file" class="form-control csv-import" data-source="file" onChange="csvUpload(this)" name="file" accept=".csv" required>
                            <button type="button" class="input-group-text btn btn-primary" for="file-input" onClick="csvUpload(this)">Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="newList" class="list-group"></div>
            </dvi>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmSave" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteModalLabel">Duplicate record(s) detected!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>The following name(s) already exists.</h1>
                        <h6 class="text-danger"><span class="names"></span></h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary include">Include duplicate record</button>
                    <button type="button" class="btn btn-danger remove">Discard duplicate record</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($settings) && $settings) : ?>
        <audio id="myAudio" preload="auto" data-starttime="<?= ($settings[0]['start']) ? $settings[0]['start'] : '' ?>" data-duration="<?= ($settings[0]['duration']) ? $settings[0]['duration'] : '' ?>">
            <source src="<?= ($settings[0]['source'] == 'f') ? '/uploads/' . $settings[0]['path'] : $settings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
        </audio>
    <?php else : ?>
        <audio id="myAudio" controls style="display:none" data-starttime="0" data-duration="10" preload="auto">
            <source src="https://youtu.be/Gb1iGDchKYs?si=fT3fFBreaYw_bh4l" type="audio/mpeg" id="audioSrc">
        </audio>
    <?php endif; ?>
    <div id="YTplayer"></div>
    <!-- Modal -->
    <div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tournament Properties</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form id="tournamentForm" class="needs-validation" method="POST" endtype="multipart/form-data">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="title">Title</span>
                            <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="title" name="title" required>
                            <div class="invalid-feedback">This field is required.</div>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="type">Elimination Type</span>
                            <select class="form-select" name="type" aria-label="type" required>
                                <option value="1" selected>Single</option>
                                <option value="2">Double</option>
                            </select>
                        </div>

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