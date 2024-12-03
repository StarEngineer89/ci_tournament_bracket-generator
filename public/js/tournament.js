function getSeconds(time) {
    const timeArray = time.split(":");

    return parseInt(timeArray[0] * 3600) + parseInt(timeArray[1] * 60) + parseInt(timeArray[2]);
}

function musicSettingToggleChange(element) {
    const settingPanel = $(element).parents('.music-setting').find('.setting');
    if ($(element).prop("checked") == true) {
        settingPanel.find('input[type="radio"], .preview input').attr('disabled', false);
        const radioElement = $(element).parent().parent().find('input[type="radio"]:checked');
        radioElement.parent().parent().children('.music-source').attr('disabled', false);
        settingPanel.removeClass('visually-hidden');

        if ($(element).data('media-type') == 0) {
            $('.toggle-music-settings').eq(2).prop('checked', false)
            musicSettingToggleChange($('.toggle-music-settings').eq(2))
            $('.toggle-music-settings').eq(2).prop('disabled', true)
        }
        if ($(element).data('media-type') == 2) {
            $('.toggle-music-settings').eq(0).prop('checked', false)
            musicSettingToggleChange($('.toggle-music-settings').eq(0))
            $('.toggle-music-settings').eq(0).prop('disabled', true)
        }
    } else {
        settingPanel.find('input[type!="hidden"]').attr('disabled', true);
        settingPanel.addClass('visually-hidden');

        if ($(element).data('media-type') == 0) {
            $('.toggle-music-settings').eq(2).prop('disabled', false)
        }
        if ($(element).data('media-type') == 2) {
            $('.toggle-music-settings').eq(0).prop('disabled', false)
        }
    }

    settingPanel.find('.duration[type="text"]').attr('disabled', true);
};

function musicSourceChange(element) {
    $(element).parents('.setting').find('.music-source').attr('disabled', true);
    const panel = $(element).parent().parent();

    if ($(element).data('target') == 'file') {
        panel.children('[data-source="file"]').attr('disabled', false);
        $(element).parents('.setting').find('.fileupload-hint').removeClass('d-none');
        $(element).parents('.setting').find('.urlupload-hint').addClass('d-none');
    }

    if ($(element).data('target') == 'url') {
        panel.children('[data-source="url"]').attr('disabled', false);
        $(element).parents('.setting').find('.fileupload-hint').addClass('d-none');
        $(element).parents('.setting').find('.urlupload-hint').removeClass('d-none');
    }

};

function musicFileUpload(element) {
    var allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mid', 'audio/x-midi'];

    if (element.files[0] && !allowedTypes.includes(element.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload audio as *.mp3, *.wav, *.midi format.')
        $("#errorModal").modal('show');

        element.value = '';
        return
    }

    if (element.files[0] && element.files[0].size > 102400000) {
        $('#errorModal .errorDetails').html('Max audio size is 100MB. Please upload small audio.')
        $("#errorModal").modal('show');
        
        element.value = '';
        return
    }

    let panel = $(element).parent();
    let index = $('.music-source[data-source="file"]').index($(element));
    $(this).parents('.music-setting').find('input[type="radio"][value="f"]').prop('checked', true);

    var formData = new FormData();
    formData.append('audio', element.files[0]);
    $.ajax({
        url: apiURL + '/tournaments/upload',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $("#processingMessage").removeClass('d-none')
        },
        success: function (data) {
            $("#processingMessage").addClass('d-none')

            if (data.errors) {
                $('#errorModal .errorDetails').html(data.errors.audio)
                $("#errorModal").modal('show');

                return false
            }
            else {
                panel.find('input[type="hidden"]').val(data.path);

                let audioElement = panel.parents('.music-setting').find('.player');
                audioElement.removeClass('d-none')
                panel.parents('.music-setting').find('.playerSource').attr('src', '/uploads/' + data.path);
                applyDuration('/uploads/' + data.path, panel.parents('.music-setting'));
                audioElement[0].load();

                if (index == 0 && document.getElementById('myAudio')) {
                    document.getElementById('audioSrc').setAttribute('src', '/uploads/' + data.path);
                    document.getElementById('myAudio').load();
                }

                panel.parents('.music-setting').find('.startAt[type="hidden"]').val(0);
                panel.parents('.music-setting').find('.startAt[type="text"]').val("00:00:00");
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function videoFileUpload(element) {
    var allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];

    if (element.files[0] && !allowedTypes.includes(element.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload video as *.mp4, *.webm, *.ogg format.')
        $("#errorModal").modal('show');

        element.value = '';
        return
    }

    if (element.files[0] && element.files[0].size > 512000000) {
        $('#errorModal .errorDetails').html('Max video size is 500MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        element.value = '';
        return
    }

    let panel = $(element).parent();
    let index = $('.music-source[data-source="file"]').index($(element));
    $(this).parents('.music-setting').find('input[type="radio"][value="f"]').prop('checked', true);

    var formData = new FormData();
    formData.append('video', element.files[0]);
    $.ajax({
        url: apiURL + '/tournaments/upload-video',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $("#processingMessage").removeClass('d-none')
        },
        success: function (data) {
            $("#processingMessage").addClass('d-none')

            if (data.errors) {
                $('#errorModal .errorDetails').html(data.errors.audio)
                $("#errorModal").modal('show');

                return false
            }
            else {
                panel.find('input[type="hidden"]').val(data.path);

                let audioElement = panel.parents('.music-setting').find('.player');
                audioElement.removeClass('d-none')
                panel.parents('.music-setting').find('.playerSource').attr('src', '/uploads/' + data.path);
                applyDuration('/uploads/' + data.path, panel.parents('.music-setting'));
                audioElement[0].load();

                if (index == 0 && document.getElementById('myAudio')) {
                    document.getElementById('audioSrc').setAttribute('src', '/uploads/' + data.path);
                    document.getElementById('myAudio').load();
                }

                panel.parents('.music-setting').find('.startAt[type="hidden"]').val(0);
                panel.parents('.music-setting').find('.startAt[type="text"]').val("00:00:00");
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function musicDurationChange(element) {
    const starttime = getSeconds($(element).parents('.preview').find('.startAt').val());
    $(element).parents('.preview').find('.startAt[type="hidden"]').val(starttime);
    const stoptime = getSeconds($(element).parents('.preview').find('.stopAt').val());
    $(element).parents('.preview').find('.stopAt[type="hidden"]').val(stoptime);

    if (starttime >= 0 && stoptime >= 0) {
        if ((stoptime - starttime) <= 0) {

        }

        $(element).parents('.preview').find('.duration').val(stoptime - starttime);
    }
}

function applyDuration(src, obj) {
    var audio = new Audio();
    $(audio).on("loadedmetadata", function () {
        const date = new Date(null);
        date.setSeconds(audio.duration);
        obj.find('.stopAt[type="hidden"]').val(audio.duration);
        obj.find('.stopAt[type="text"]').val(date.toISOString().slice(11, 19));
        obj.find('.duration').val(audio.duration);
    });
    audio.src = src;
}
