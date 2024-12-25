<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/themes/nano.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/pickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- Popperjs -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha256-BRqBN7dYgABqtY9Hd4ynE+1slnEw+roEPFzQ7TRRfcg=" crossorigin="anonymous"></script>
<!-- Tempus Dominus JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
<script>
var hash = '<?= uniqid(rand(), TRUE);?>';
</script>
<script src="/js/participants.js"></script>
<script src="/js/tournament.js"></script>
<!-- <script src="/js/player.js"></script> -->
<script type="text/javascript">
let eleminationType;
let tournament_id = '<?= (isset($tournament)) ? $tournament['id'] : null ?>';
var user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>;
let shuffle_duration = 10;
let audio = document.getElementById("myAudio");
let audioStartTime = 0;
let duplicates = [];
let insert_count = 0;
let ptNames
let filteredNames
let videoPlayer = document.getElementById('videoPlayer');
let videoStartTime = 0;
let video_duration = 10;

var enable_confirmPopup = false;

const itemList = document.getElementById('newList');

$(window).on('load', function() {
    $("#preview").fadeIn();
});
$(document).ready(function() {

    const linkedPicker1Element = document.getElementById('startAvPicker');
    const linked1 = new tempusDominus.TempusDominus(linkedPicker1Element, {
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        },
        restrictions: {
            minDate: new Date(),
        },
    });
    const linked2 = new tempusDominus.TempusDominus(document.getElementById('endAvPicker'), {
        useCurrent: false,
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        }
    });

    //using event listeners
    linkedPicker1Element.addEventListener('change.td', (e) => {
        linked2.updateOptions({
            restrictions: {
                minDate: e.detail.date,
            },
        });
    });

    //using subscribe method
    const subscription = linked2.subscribe('change.td', (e) => {
        linked1.updateOptions({
            restrictions: {
                maxDate: e.date,
            },
        });
    });
    $("textarea#description").summernote({
        callbacks: {
            onMediaDelete: function(target) {
                // Handle media deletion if needed
            },
            onVideoInsert: function(target) {
                $(target).wrap('<div class="responsive-video"></div>');
            },
            onVideoUpload: function(files) {
                uploadVideo(files[0]);
            }

        }
    });

    <?php if (isset($participants)): ?>
    var participants = JSON.parse('<?= $participants ?>')
    renderParticipants(participants)
    <?php endif ?>


    /** Submit the tournament settings when create the new tournament */
    $('#submit').on('click', function(event) {
        const form = document.getElementById('tournamentForm');

        let isValid = true;

        if (document.getElementById('enableAvailability').checked) {
            let startDateInput = document.getElementById('startAvPickerInput')
            let endDateInput = document.getElementById('endAvPickerInput')

            if (!startDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('startAvPicker').classList.add("is-invalid");
                startDateInput.addEventListener('change', () => {
                    if (startDateInput.value.trim()) {
                        document.getElementById('startAvPicker').classList.remove("is-invalid");
                    }
                })
                startDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission
            }

            if (!endDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('endAvPicker').classList.add("is-invalid");
                endDateInput.addEventListener('change', () => {
                    if (endDateInput.value.trim()) {
                        document.getElementById('endAvPicker').classList.remove("is-invalid");
                    }
                })
                endDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission
            }

            const currentDate = new Date()
            const startDate = new Date(document.getElementById('startAvPickerInput').value)
            const endDate = new Date(document.getElementById('endAvPickerInput').value)

            if (startDate > endDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "Stop date must be greater than start date."
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            }

            if (startDate < currentDate) {
                isValid = false
                document.getElementById('availability-start-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-start-date-error').textContent = "You cannot select a past date/time!"
                document.getElementById('availability-start-date-error').classList.remove('d-none')
            } else {
                document.getElementById('availability-start-date-error').classList.add('d-none')
            }

            if (endDate < currentDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "You cannot select a past date/time!"
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            } else {
                document.getElementById('availability-end-date-error').classList.add('d-none')
            }
        }

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

        if (!isValid || !form.checkValidity()) {
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

        $.ajax({
            url: apiURL + '/tournaments/save',
            type: "POST",
            data: data,
            beforeSend: function() {
                //$("#preview").fadeOut();
                $('#tournamentSettings').modal('hide');
                $('#beforeProcessing').addClass('generateProcessing')
                $('#beforeProcessing').removeClass('d-none')
                $("#err").fadeOut();
            },
            success: function(result) {
                $('#beforeProcessing').addClass('d-none')
                $('#beforeProcessing').removeClass('generateProcessing')
                var result = JSON.parse(result);
                if (result.error) {
                    // invalid file format.
                    $("#err").html("Invalid File !").fadeIn();
                } else {
                    tournament_id = result.data.tournament_id;
                    eleminationType = parseInt(result.data.type);

                    if (result.data.music) {
                        if (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>]) {
                            shuffle_duration = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].duration) ? parseInt(result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].duration) : 10;
                            audioStartTime = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].start) ? parseInt(result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].start) : 10;
                            let audioSrc = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].source == 'f') ? '<?= base_url('uploads/') ?>' : '<?= base_url('uploads/') ?>';
                            audioSrc += result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION ?>].path;

                            $('#audioSrc').attr('src', audioSrc);

                            audio.load();
                            audio.addEventListener('loadedmetadata', function() {
                                audio.currentTime = audioStartTime;
                                audio.play();
                            });

                            document.getElementById('stopMusicButton').classList.remove('d-none');
                            document.getElementById('stopMusicButton').addEventListener('click', function() {
                                stopMusicPlaying()
                            });
                        }

                        if (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]) {
                            shuffle_duration = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].duration) ? parseInt(result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].duration) : 10;
                            videoStartTime = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].start) ? parseInt(result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].start) : 10;
                            video_duration = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].duration) ? parseInt(result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].duration) : video_duration;
                            let videoSrc = (result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].source == 'f') ? '<?= base_url('uploads/') ?>' : '<?= base_url('uploads/') ?>';
                            videoSrc += result.data.music[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>].path;

                            $('#videoPlayer').attr('src', videoSrc)

                            videoPlayer.load();
                            videoPlayer.classList.remove('d-none')
                            videoPlayer.addEventListener('loadedmetadata', function() {
                                videoPlayer.currentTime = videoStartTime;
                                videoPlayer.play();
                            });

                            document.getElementById('stopVideoButton').classList.remove('d-none');
                            document.getElementById('stopVideoButton').addEventListener('click', function() {
                                stopVideoPlaying()
                            });
                        }
                    }

                    if ($('#enableShuffle').prop('checked') || (result.data.music !== undefined && result.data.music[0] !== undefined)) {
                        document.getElementById('skipShuffleButton').classList.remove('d-none');
                        document.getElementById('skipShuffleButton').addEventListener('click', function() {
                            skipShuffling()
                        });
                    }

                    let enableShuffling = true
                    if ($('#enableShuffle')) {
                        enableShuffling = $('#enableShuffle').prop('checked')
                    }

                    callShuffle(enableShuffling);
                }
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    });

    $('#generate').on('click', function() {
        if (itemList.children.length < 2) {
            $('#generateErrorModal').modal('show')
            return false;
        }

        document.getElementsByClassName('participants-box')[0].scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest"
        })

        <?php if (isset($tournament) && count($tournament)) : ?>
        tournament_id = "<?= $tournament['id'] ?>";
        eleminationType = <?= intval($tournament['type']) ?>;

        /** Music player setting */
        <?php if (isset($settings) && isset($settings[MUSIC_TYPE_BRACKET_GENERATION])) : ?>
        audio.load();
        audio.currentTime = parseInt(<?= $settings[MUSIC_TYPE_BRACKET_GENERATION]['start'] ? $settings[MUSIC_TYPE_BRACKET_GENERATION]['start'] : 0 ?>);
        audio.play()
        shuffle_duration = parseInt(<?= $settings[MUSIC_TYPE_BRACKET_GENERATION]['duration'] ?>);

        document.getElementById('stopMusicButton').classList.remove('d-none');
        document.getElementById('stopMusicButton').addEventListener('click', function() {
            stopMusicPlaying()
        });
        <?php endif; ?>

        /** Video player setting */
        <?php if (isset($settings) && isset($settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO])) : ?>
        videoPlayer.classList.remove('d-none')
        videoPlayer.currentTime = parseInt(<?= $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['start'] ? $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['start'] : 0 ?>);
        videoPlayer.play()
        shuffle_duration = parseInt(<?= $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['duration'] ?>);

        document.getElementById('stopVideoButton').classList.remove('d-none');
        document.getElementById('stopVideoButton').addEventListener('click', function() {
            stopVideoPlaying()
        });
        <?php endif; ?>

        document.getElementById('skipShuffleButton').classList.remove('d-none');
        document.getElementById('skipShuffleButton').addEventListener('click', function() {
            skipShuffling()
        });

        let shuffle_enable = 0
        <?php if ($tournament['shuffle_enabled']): ?>
        shuffle_enable = 1
        <?php endif ?>
        callShuffle(shuffle_enable);

        <?php else : ?>
        $('#tournamentSettings').modal('show');
        <?php endif; ?>
    });

    $('#addParticipants').on('click', function() {
        var opts = $('#participantNames').val();

        if (opts == '') {
            return false;
        }

        ptNames = opts.replaceAll(', ', ',').split(',');

        let validatedParticipantNames = validateParticipantNames(ptNames)
        let duplicatedNames = validatedParticipantNames.duplicates
        filteredNames = validatedParticipantNames.validNames

        if (duplicatedNames.length) {
            $('#confirmSave .names').html(duplicatedNames.join(', '));
            $('#confirmSave').modal('show');

            return false;
        }

        <?php if (isset($tournament)): ?>
        const tournament_id = <?= $tournament['id'] ?>;
        <?php endif ?>
        if (ptNames.length) {
            addParticipants({
                names: ptNames,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                tournament_id: tournament_id
            });
        }
    });

    $('#confirmSave .include').on('click', () => {
        <?php if (isset($tournament)): ?>
        const tournament_id = <?= $tournament['id'] ?>;
        <?php endif ?>
        if (ptNames.length) {
            addParticipants({
                names: ptNames,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                tournament_id: tournament_id
            });
        } else {
            $('#confirmSave').modal('hide')
        }
    })

    $('#confirmSave .remove').on('click', () => {
        <?php if (isset($tournament)): ?>
        const tournament_id = <?= $tournament['id'] ?>;
        <?php endif ?>
        if (filteredNames.length) {
            addParticipants({
                names: filteredNames,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                tournament_id: tournament_id
            });
        } else {
            $('#confirmSave').modal('hide')
        }

        appendAlert('Duplicate records discarded!', 'success');
    })

    $('#clearParticipantsConfirmBtn').on('click', () => {
        let items = $('#newList').children();
        if (!items.length) {
            appendAlert('There is no participants to clear.', 'danger');
            $('#clearParticipantsConfirmModal').modal('hide')

            return false;
        }

        let ajax_url = apiURL + '/participants/clear'
        <?php if (isset($tournament)): ?>
        ajax_url = apiURL + '/participants/clear?t_id=' + '<?= $tournament['id'] ?>'
        <?php endif ?>

        $.ajax({
            type: "GET",
            url: ajax_url,
            data: {
                'hash': hash
            },
            success: function(result) {
                result = JSON.parse(result);

                if (result.result == 'success') {
                    $('#newList').html('')
                    $('#indexList').html('')
                    $('.empty-message-wrapper').removeClass('d-none')
                    $('#clearParticipantsConfirmModal').modal('hide')
                    appendAlert('Participant list cleared!', 'success');
                }
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })

    $('#checkDuplicationBtn').on('click', function() {
        var items = $('#newList span.p-name')
        const names = _.map(items, (ele) => {
            return {
                'id': ele.parentElement.dataset.id,
                'name': ele.textContent,
                'lowercase': ele.textContent.replace(/\s+/g, '').toLowerCase()
            }
        })

        if (!names.length) {
            return false;
        }

        let groupedNames = _.chain(names).groupBy('lowercase').filter(function(v) {
            return v.length > 1
        }).flatten().uniq().value()

        if (groupedNames.length) {
            const groupedByLowercase = Object.values(
                groupedNames.reduce((acc, name) => {
                    if (!acc[name.lowercase]) {
                        acc[name.lowercase] = [];
                    }
                    acc[name.lowercase].push(name);
                    return acc;
                }, {})
            );

            let elements = []
            let duplicatedParticipants = []
            duplicates = []
            groupedByLowercase.forEach(group => {
                group.forEach(ele => {
                    if (elements[ele.lowercase]) {
                        duplicates.push(ele.id)
                        duplicatedParticipants.push(ele)
                    } else {
                        elements[ele.lowercase] = ele
                    }
                })
            })

            duplications = _.map(_.uniq(duplicatedParticipants, function(item) {
                return item.name;
            }), function(item) {
                return item.name
            })

            duplicate_names = duplications.join(", ")
            $('#removeDuplicationsConfirmModal span.names').html(duplicate_names)
            $('#removeDuplicationsConfirmModal').modal('show')
        } else {
            appendAlert('No duplicates detected.', 'success');
        }

    });

    $('#removeDuplicationsConfirmBtn').on('click', function() {
        $.ajax({
            type: "POST",
            url: apiURL + '/participants/deletes',
            data: {
                'p_ids': duplicates,
                'tournament_id': 0,
                'hash': hash
            },
            dataType: "JSON",
            beforeSend: function() {
                $('#beforeProcessing').removeClass('d-none')
            },
            success: function(result) {
                $('#beforeProcessing').addClass('d-none')
                $('#removeDuplicationsConfirmModal').modal('hide')
                if (result.count) {
                    renderParticipants(result.participants);

                    $('#participantNames').val(null);
                    $('input.csv-import').val(null)
                    $('#confirmSave').modal('hide');
                    $('#collapseAddParticipant').removeClass('show');

                    appendAlert('Duplicate record(s) removed!', 'success');
                }

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
    })

    const selectBackgroundColorModal = document.getElementById('selectBackgroundColorModal');
    if (selectBackgroundColorModal) {
        selectBackgroundColorModal.addEventListener('show.bs.modal', event => {
            selectBackgroundColorModal.setAttribute('data-setting-id', event.relatedTarget.getAttribute('data-setting-id'));
        })
    }

    $('#selectBackgroundColorConfirmBtn').on('click', function() {
        const color = $('#bgColorInput').val()
        const settingId = $(selectBackgroundColorModal).data('setting-id')

        $.ajax({
            type: "POST",
            url: apiURL + '/usersettings/save',
            data: {
                id: settingId,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                setting_name: '<?= USERSETTING_PARTICIPANTSLIST_BG_COLOR ?>',
                setting_value: color
            },
            success: function(result) {
                $('.participant-list').css('background-color', color)
                $(selectBackgroundColorModal).modal('hide')
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })

    const selectTournamentModal = document.getElementById('selectTournamentModal');
    if (selectTournamentModal) {
        selectTournamentModal.addEventListener('show.bs.modal', event => {
            selectTournamentModal.setAttribute('data-setting-id', event.relatedTarget.getAttribute('data-id'));

            drawTournamentsTable()
        })
    }

    $('#selectTournamentConfirmBtn').on('click', function() {
        const tournament_id = selectTournamentConfirmModal.dataset.id
        performReuseParticipants(tournament_id);
    })

});
document.addEventListener('DOMContentLoaded', (event) => {
    const pickr = Pickr.create({
        el: '#color-picker-button',
        theme: 'nano', // or 'monolith', or 'nano'
        default: '<?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] : '' ?>',

        swatches: [
            'rgba(244, 67, 54, 1)',
            'rgba(233, 30, 99, 0.95)',
            'rgba(156, 39, 176, 0.9)',
            'rgba(103, 58, 183, 0.85)',
            'rgba(63, 81, 181, 0.8)',
            'rgba(33, 150, 243, 0.75)',
            'rgba(3, 169, 244, 0.7)',
            'rgba(0, 188, 212, 0.7)',
            'rgba(0, 150, 136, 0.75)',
            'rgba(76, 175, 80, 0.8)',
            'rgba(139, 195, 74, 0.85)',
            'rgba(205, 220, 57, 0.9)',
            'rgba(255, 235, 59, 0.95)',
            'rgba(255, 193, 7, 1)'
        ],

        components: {

            // Main components
            preview: true,
            opacity: true,
            hue: true,

            // Input / output Options
            interaction: {
                hex: true,
                rgba: true,
                hsla: true,
                hsva: true,
                cmyk: true,
                input: true,
                clear: true,
                save: true
            }
        },
        i18n: {
            'btn:save': 'Apply',
        }
    });

    $('.pcr-interaction .pcr-save').on('click', function() {
        const rgbaColor = pickr.getColor().toRGBA().toString();
        $('.participant-list').css('background-color', rgbaColor)
        $('#bgColorInput').val(pickr.getColor().toRGBA().toString())
        $('.pcr-app').removeClass('visible')
    })
});

var csvUpload = (element) => {
    // Validate file type
    const allowedExtensions = ['text/csv'];
    const fileExtension = $('.csv-import')[0].files[0].type;
    if (!allowedExtensions.includes(fileExtension)) {
        $('#errorModal .errorDetails').html('Please upload a CSV file.')
        $("#errorModal").modal('show');

        return false;
    }

    if (!$('.csv-import').val()) {
        $('.csv-import').addClass('is-invalid');
        return false;
    }
    $('.csv-import').removeClass('is-invalid');
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
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')

            if (result.errors) {
                $('#errorModal .errorDetails').html(result.errors.file)
                $("#errorModal").modal('show');

                return false
            }

            ptNames = result.names
            let validatedParticipantNames = validateParticipantNames(ptNames)
            let duplicatedNames = validatedParticipantNames.duplicates
            filteredNames = validatedParticipantNames.validNames

            if (duplicatedNames.length) {
                $('#confirmSave .names').html(duplicatedNames.join(', '));
                $('#confirmSave').modal('show');

                return false;
            }

            if (result.names.length) {
                <?php if (isset($tournament)): ?>
                const tournament_id = <?= $tournament['id'] ?>;
                <?php endif ?>
                addParticipants({
                    names: ptNames,
                    user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                    tournament_id: tournament_id
                });
            }
        },
        error: function(e) {
            $('#errorModal .errorDetails').html(e)
            $("#errorModal").show();
        }
    });
}

var tournamentsTable = $('#tournamentTable')
var drawTournamentsTable = () => {
    // Check if the DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#tournamentTable')) {
        // Destroy the existing DataTable before reinitializing it
        tournamentsTable.destroy();
    }

    tournamentsTable = $('#tournamentTable').DataTable({
        "searching": true,
        "processing": true,
        "ajax": {
            "url": apiURL + '/tournaments/get-list',
            "type": "POST",
            "dataSrc": "",
            "data": function(d) {
                d.user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>; // Include the user_id parameter
                d.search_tournament = $('#searchTournament').val();
            }
        },
        scrollX: true,
        "columns": [{
                "data": null,
                "render": function(data, type, row, meta) {
                    return meta.row + 1; // Display index number
                }
            },
            {
                "data": "name"
            },
            {
                "data": "type",
                "render": function(data, type, row, meta) {
                    var type = 'Single'
                    if (row.type == <?= TOURNAMENT_TYPE_DOUBLE ?>) {
                        type = "Double"
                    }

                    if (row.type == <?= TOURNAMENT_TYPE_KNOCKOUT ?>) {
                        type = "Knockout"
                    }

                    return type;
                }
            },
            {
                "data": "status",
                "render": function(data, type, row, meta) {
                    var status = 'In progress'
                    if (row.status == <?= TOURNAMENT_STATUS_COMPLETED ?>) {
                        status = 'Completed'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_ABANDONED ?>) {
                        status = 'Abandoned'
                    }

                    return status;
                }
            },
            {
                "data": null,
                "render": function(data, type, row, meta) {
                    return `
                        <a class="edit-btn" data-tournament-id="${row.id}" data-name="${row.name}" onClick="reuseParticipant(this)">Reuse</a>
                    `;
                }
            }
        ],
        "columnDefs": [{
            "orderable": false,
            "targets": [2, 3, 4]
        }],
    });

    $('#searchTournamentBtn').on('click', function() {
        tournamentsTable.ajax.reload();
    });

    $('#typeFilter').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        tournamentsTable.columns(2).search(selectedType).draw();
    });

    $('#stautsFilter').on('change', function() {
        var selectedStatus = $(this).val().toLowerCase();
        tournamentsTable.columns(3).search(selectedStatus).draw();
    });

}

var handleKeyPress = (event) => {
    tournamentsTable.ajax.reload()
}

var reuseParticipant = (element) => {
    if ($('.participant-list .list-group-item').length) {
        const selectTournamentConfirmModal = document.getElementById('selectTournamentConfirmModal');
        selectTournamentConfirmModal.dataset.id = element.dataset.tournamentId
        var tournamentNameElement = selectTournamentConfirmModal.querySelector('.tournament-name')
        tournamentNameElement.textContent = element.dataset.name
        $('#selectTournamentConfirmModal').modal('show')
    } else {
        performReuseParticipants(element.dataset.tournamentId)
    }
}
var performReuseParticipants = (tournament_id = null) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/tournaments/reuse-participants',
        data: {
            id: tournament_id,
            hash: hash
        },
        beforeSend: function() {
            //$("#preview").fadeOut();
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            renderParticipants(result)
            $(selectTournamentConfirmModal).modal('hide')
            $(selectTournamentModal).modal('hide')
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

<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card shadow-sm">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('tournaments') ?>">Dashboard</a></li>
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

        <h5 class="card-title d-flex justify-content-center">
            <? //= lang('Auth.login') ?>Tournament Participants
        </h5>

        <div id="liveAlertPlaceholder"></div>

        <div class="participants-box m-auto">
            <div class="buttons d-flex justify-content-center">
                <button id="add-participant" class="btn btn-default" data-bs-toggle="collapse" data-bs-target="#collapseAddParticipant" aria-expanded="false" aria-controls="collapseAddParticipant">Add Participant</button>
                <button id="generate" class="btn btn-default">Generate Brackets</button>
                <a class="btn btn-default dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-gear"></i> Additional Options
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button id="clearParticipant" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#clearParticipantsConfirmModal">Clear Participant(s) List</button></li>
                    <li><button id="checkDuplicationBtn" class="btn btn-default">Check Duplicates</button></li>
                    <li><button id="checkDuplicationBtn" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#selectTournamentModal" data-id="<?= (isset($tournament)) ? $tournament['id'] : '' ?>">Reuse Participants</button></li>
                    <li><button id="changeBackgroundColor" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#selectBackgroundColorModal" data-id="<?= (isset($tournament)) ? $tournament['id'] : '' ?>">Change Background</button></li>
                </ul>
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
                        Or, upload a csv file of participant names. <a href="<?= base_url('/uploads/CSV/Samples/sample.csv') ?>">Download sample template file</a>
                        <br />
                        Note that the first row header, as well as any other columns besides 1st column are ignored.
                    </div>

                    <form class="row row-cols-lg-auto g-3 align-items-center mt-1" enctype="multipart/form-data" method="post">
                        <div class="input-group mb-3">
                            <input type="file" class="form-control csv-import" data-source="file" name="file" accept=".csv" required>
                            <button type="button" class="input-group-text btn btn-primary" for="file-input" onClick="csvUpload(this)"><i class="fa fa-upload"></i> Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="participant-list d-flex flex-wrap" <?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? 'style="background-color: ' . $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] . '"' : '' ?>>
                <div class="empty-message-wrapper col-12 p-2 text-bg-info rounded">
                    <p class="text-center">Wow, such empty!</p>
                    <p> To get started, "Add Participants" or from Additional Options, "Reuse Participants" from another tournament.</p>
                    <p> Once you've populated the participants list, proceed with the "Generate Brackets" option to generate the tournament!</p>
                    <p> FYI, you may right click (or hold on mobile) to edit/delete individual participants here.</p>
                    <p> Btw, if you want to personalize your participants with images, you may do so here or in the tournament brackets by clicking the placeholder photo icon.</p>
                </div>

                <div class="video-wrapper col-12">
                    <?php if (isset($settings) && isset($settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO])) : ?>
                    <video id="videoPlayer" class="col-12 d-none" preload="auto" data-starttime="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['start']) ? $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['start'] : '' ?>" data-duration="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['duration']) ? $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['duration'] : '' ?>">
                        <source src="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['source'] == 'f') ? '/uploads/' . $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['path'] : '/uploads/' . $settings[MUSIC_TYPE_BRACKET_GENERATION_VIDEO]['path'] ?>" type="audio/mpeg" id="audioSrc">
                    </video>
                    <?php else: ?>
                    <video id="videoPlayer" class="col-12 d-none"></video>
                    <?php endif; ?>
                </div>

                <div class="col-12 d-flex">
                    <div id="indexList" class="list-group col-auto"></div>
                    <div id="newList" class="list-group col-10"></div>
                </div>

            </div>
        </div>
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
                <button type="button" class="btn btn-primary signin">Signup/Signin to preserve tournament</button>
                <button type="button" class="btn btn-danger leave">Discard</button>
            </div>
        </div>
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
                <button type="button" class="btn btn-primary include">Include duplicate record(s)</button>
                <button type="button" class="btn btn-danger remove">Discard duplicate record(s)</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="tournamentSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="tournamentSettingsModalLabel">Tournament Properties</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="tournamentForm" class="create-settings needs-validation" method="POST" endtype="multipart/form-data">
                    <?= $settingsBlock ?>

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

<!-- Modal -->
<div class="modal fade" id="clearParticipantsConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clearParticipantsConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Are you sure you want to clear the participants list?</h4>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="clearParticipantsConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectBackgroundColorModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectBackgroundColorModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectBackgroundColorModalLabel">Choose the background color in participants list</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center align-items-center">
                    <label for="bgColorInput" class="form-label me-2">Choose a Background Color:</label>
                    <input type="hidden" class="form-control form-control-color" id="bgColorInput" value="<?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] : '' ?>" title="Choose your color">
                    <button id="color-picker-button"></button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectBackgroundColorConfirmBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectTournamentModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectTournamentModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectTournamentModalLabel">Select the tournament to reuse.</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="searchTournament" onkeydown="handleKeyPress(event)">
                        <button id="searchTournamentBtn" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
                <div class="tournaments-table">
                    <table id="tournamentTable" class="table-responsive display col-12" style="width: 100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>
                                    <label for="typeFilter">Type:</label>
                                    <select id="typeFilter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Knockout">Knockout</option>
                                    </select>
                                </th>
                                <th>
                                    <label for="statusFilter">Status:</label>
                                    <select id="stautsFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="In progress">In progress</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Abandoned">Abandoned</option>
                                    </select>
                                </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectTournamentConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectTournamentConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectTournamentConfirmModalLabel">Confirmation Message</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                <h6>Upon confirmation, the participants list will be overwritten with tournament "<span class="tournament-name"></span>"'s participants list.</h6>
                </p>
                <p class="mt-3">Are you sure you want to proceed?
                <h6 class="text-danger">This action cannot be undone!</h6>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Discard</button>
                <button type="button" class="btn btn-primary" id="selectTournamentConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="generateErrorModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="generateErrorModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="generateErrorModalLabel">Alert</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Please populate the participant list first before generating the brackets.</h6>
                <h6>There should be at least 2 or more participants.</h6>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="removeDuplicationsConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="removeDuplicationsConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="removeDuplicationsConfirmModalLabel">Confirm to remove duplicates</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                <h6>The following duplicate participants were found.<br /><span class="names"></span></h6>
                </p>
                <p class="mt-3">Are you sure you want to proceed?
                <h6 class="text-danger">This action cannot be undone!</h6>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Discard</button>
                <button type="button" class="btn btn-danger" id="removeDuplicationsConfirmBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($settings) && isset($settings[MUSIC_TYPE_BRACKET_GENERATION])) : ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION]['start']) ? $settings[MUSIC_TYPE_BRACKET_GENERATION]['start'] : '' ?>" data-duration="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION]['duration']) ? $settings[MUSIC_TYPE_BRACKET_GENERATION]['duration'] : '' ?>">
    <source src="<?= ($settings[MUSIC_TYPE_BRACKET_GENERATION]['source'] == 'f') ? '/uploads/' . $settings[MUSIC_TYPE_BRACKET_GENERATION]['path'] : '/uploads/' . $settings[MUSIC_TYPE_BRACKET_GENERATION]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>
<?php else : ?>
<audio id="myAudio" preload="auto">
    <source src="" type="audio/mpeg" id="audioSrc">
</audio>
<?php endif; ?>

<div class="buttons skipButtons">
    <button id="skipShuffleButton" class="d-none">Skip</button>
    <button id="stopMusicButton" class="d-none">Pause Music</button>
    <button id="stopVideoButton" class="d-none">Pause Video</button>
</div>

<div id="generateProcessing" class="overlay d-none">
    <div class="snippet p-3 .bg-light" data-title="dot-elastic">
        <p>Generating Tournament Brackets...</p>
        <div class="stage">
            <div class="dot-elastic"></div>
        </div>
    </div>
</div>

<div id="processingMessage" class="overlay d-none">
    <div class="snippet p-3 .bg-light" data-title="dot-elastic">
        <p>Processing, please wait...</p>
        <div class="stage">
            <div class="dot-elastic"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>