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
    } else {
        settingPanel.find('input[type!="hidden"]').attr('disabled', true);
        settingPanel.addClass('visually-hidden');
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
    let panel = $(element).parent();
    let index = $('.music-source[data-source="file"]').index($(element));
    $(this).parents('.music-setting').find('input[type="radio"][value="f"]').prop('checked', true);

    var formData = new FormData();
    formData.append('audio', $(element)[0].files[0]);
    $.ajax({
        url: apiURL + '/tournaments/upload',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $("#err").fadeOut();
        },
        success: function (data) {
            var data = JSON.parse(data);
            if (data.error) {
                // invalid file format.
                $("#err").html("Invalid File !").fadeIn();
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
