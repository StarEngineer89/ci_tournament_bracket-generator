
 setCookie = (name, value, days) => {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

let acceptCookies = () => {
    setCookie('cookie_consent', 'accepted', 365);
    document.getElementById('cookieConsentModal').style.display = 'none';
}

let rejectCookies = () => {
    setCookie('cookie_consent', 'rejected', 365);
    document.getElementById('cookieConsentModal').style.display = 'none';
    alert('Cookies rejected. To reactivate, clear your browser history and visit the site again.');
}

let appendAlert = (message, type) => {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    if (alertPlaceholder) {
        alertPlaceholder.innerHTML = ''
        const wrapper = document.createElement('div')

        if (Array.isArray(message)) {
            wrapper.innerHTML = ''
            message.forEach((item, i) => {
                wrapper.innerHTML += [
                    `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                    `   <div>${item}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    '</div>'
                ].join('')
            })
        } else {
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                `   <div>${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('')
        }

        alertPlaceholder.append(wrapper)

        $("div.alert").fadeTo(5000, 500).slideUp(500, function() {
            $("div.alert").slideUp(500);
        });
    }
}

let appendNotification = (message, type) => {
    const notificationPlaceholder = document.getElementById('notificationAlertPlaceholder')
    if (notificationPlaceholder) {
        notificationPlaceholder.innerHTML = ''
        const wrapper = document.createElement('div')

        if (Array.isArray(message)) {
            wrapper.innerHTML = ''
            message.forEach((item, i) => {
                wrapper.innerHTML += [
                    `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                    `   <div>${item}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    '</div>'
                ].join('')
            })
        } else {
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible position-fixed top-1 end-0 z-3 me-3 mt-1" role="alert">`,
                `   <div class="d-flex">${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('')
        }

        notificationPlaceholder.append(wrapper)

        $("div.alert").fadeTo(3000, 500).slideUp(500, function() {
            $("div.alert").slideUp(500);
        });
    }

}

let readNotification = (notificationElement) => {
    const link = $(notificationElement).data('link')
    const notificationId = $(notificationElement).data('id')

    $.ajax({
        type: "put",
        url: `${apiURL}/notifications/mark-as-read/${notificationId}`,
        success: function(result) {
            $(notificationElement).remove()
            window.location.href = link
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

let deleteNotification = (notificationElement) => {
    const link = $(notificationElement).data('link')
    const notificationId = $(notificationElement).data('id')

    $.ajax({
        type: "delete",
        url: `${apiURL}/notifications/delete/${notificationId}`,
        success: function(result) {
            $(notificationElement).remove()
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
    
let toggleShuffleParticipants = (checkbox) => {
    var enableShufflingHint = document.querySelector('.enable-shuffling-hint');
    var disableShufflingHint = document.querySelector('.disable-shuffling-hint');

    if (checkbox.checked) {
        enableShufflingHint.classList.remove('d-none');
        disableShufflingHint.classList.add('d-none');
    } else {
        enableShufflingHint.classList.add('d-none');
        disableShufflingHint.classList.remove('d-none');
    }
}

let stopMusicPlaying = () => {
    // Your code to stop music goes here
    const audio = document.getElementById('myAudio');

    if (audio.paused) {
        audio.play();
        document.getElementById('stopMusicButton').textContent = "Pause Music"
    } else {
        audio.pause();
        document.getElementById('stopMusicButton').textContent = "Resume Music"
    }
}

let saveGeneralSettings = () => {
    form = $('#settingsForm')

  $.ajax({
    url: apiURL + '/usersettings/save',
    type: 'POST',
    data: form.serialize(),
    success: function(response) {
      if (response.status == 'success') {
        // Close the modal
        $('#settingsModal').modal('hide');
      } else {
        alert('Failed to save settings');
        }
        $('#settingsModal').modal('hide')
    },
    error: function() {
      alert('An error occurred while saving the settings');
    }
  });
}

$(document).ready(function () {
    const timezoneSelect = $('#timezone');
    const timezones = moment.tz.names();

    timezones.forEach(timezone => {
        const option = new Option(timezone, timezone);
        if (timezone === defaultTimezone) {
            option.selected = true;
        }
        timezoneSelect.append(option);
    });

    timezoneSelect.on('change', function() {
        const selectedTimezone = $(this).val();
        updateTime(selectedTimezone);

        let currentYear = new Date().getFullYear();
        let dstStart = getSecondSundayOfMarch(currentYear, selectedTimezone);
        const formattedDate = formatDateToTimeZone(dstStart, selectedTimezone);

        // Update other timezone information if needed
        $('#timezoneStatus').text(`This timezone is currently in ${selectedTimezone}.`);
        $('#daylightSaving').text(`Daylight saving time begins on: ${formattedDate}.`);
    });
    $('[data-toggle="tooltip"]').tooltip();
})

function getSecondSundayOfMarch(year, timeZone) {
    // Helper function to convert local date to a given timezone
    function toTimeZone(date, timeZone) {
        return new Date(date.toLocaleString('en-US', { timeZone }));
    }

    // Get the local date for March 1st of the given year
    let localDate = new Date(year, 2, 1);

    // Convert the local date to the specified timezone
    let tzDate = toTimeZone(localDate, timeZone);

    // Get the day of the week (0-6, where 0 is Sunday)
    let day = tzDate.getUTCDay();

    // Calculate the second Sunday of March
    let secondSunday = 7 + (7 - day) % 7 + 1;

    // Create a new date for the second Sunday in the specified timezone
    let secondSundayDate = new Date(Date.UTC(year, 2, secondSunday));

    // Convert back to the specified timezone
    let finalDate = toTimeZone(secondSundayDate, timeZone);

    return finalDate;
}

function formatDateToTimeZone(date, timeZone) {
    return date.toLocaleString('en-US', {
        timeZone,
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        timeZoneName: 'short'
    });
}

function formatTime(date, options) {
    return new Intl.DateTimeFormat('en-US', options).format(date);
}

function updateTime(selectedTimezone) {
    const utcDate = new Date().toLocaleString("en-US", { timeZone: 'UTC' });
    const localDate = new Date().toLocaleString("en-US", { timeZone: selectedTimezone });
    const formattedUtcTime = formatTime(new Date(utcDate), { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const formattedLocalTime = formatTime(new Date(localDate), { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    $('#utcTime').text(formattedUtcTime);
    $('#localTime').text(formattedLocalTime);
}

let toggleScoreOption = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('#scorePerBracket').prop('disabled', false)
        //$('#incrementScore').prop('disabled', false)
        $('#scoreOptions').removeClass('d-none')
        $('.enable-scoreoption-hint').removeClass('d-none')
    } else {
        $('#scorePerBracket').prop('disabled', true)
        $('#incrementScore').prop('disabled', true)
        $('#scoreOptions').addClass('d-none')
        $('.enable-scoreoption-hint').addClass('d-none')
    }
}

let toggleIncrementScore = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('#incrementScore').prop('disabled', false)
        $('.enable-increamentscoreoption-hint').removeClass('d-none')
    } else {
        $('#incrementScore').prop('disabled', true)
        $('.enable-increamentscoreoption-hint').addClass('d-none')
    }
}

const enableDescriptionEdit = (button) => {
    const descriptionDiv = button.parentElement.querySelector('.description')
    const originalText = descriptionDiv.innerHTML
    originalDescriptionContent = originalText
    descriptionDiv.innerHTML = `<div id="summernote">${originalText}</div>`

    $('#summernote').summernote({
        height: 400,
        callbacks: {
            onMediaDelete: function(target) {
                // Handle media deletion if needed
            },
            onVideoInsert: function(target) {
                $(target).wrap('<div class="responsive-video"></div>');
            }
        }
    })

    let buttonsWrapper = document.createElement('div')
    buttonsWrapper.className = 'd-flex justify-content-end mt-3'

    const saveButton = document.createElement('button')
    saveButton.innerText = 'Save'
    saveButton.className = 'btn btn-primary'
    saveButton.onclick = () => {
        newDescriptionContent = $('#summernote').summernote('code')
        currentDescriptionDiv = descriptionDiv
        $('#saveDescriptionConfirmModal').modal('show')
    }

    const dismissButton = document.createElement('button')
    dismissButton.innerText = 'Discard'
    dismissButton.className = 'btn btn-secondary ms-2'
    dismissButton.onclick = () => {
        currentDescriptionDiv = descriptionDiv
        $('#dismissDescriptionEditConfirmModal').modal('show')
    }

    buttonsWrapper.append(saveButton)
    buttonsWrapper.append(dismissButton)

    descriptionDiv.append(buttonsWrapper)
    
    document.getElementById('editDescriptionBtn').classList.add('d-none')
}

const saveDescription = () => {
    $.ajax({
        url: apiURL + `/tournaments/${tournament_id}/update`,
        type: 'POST',
        data: {
            description: newDescriptionContent
        },
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
            $('#saveDescriptionConfirmModal').modal('hide')
        },
        success: function(response) {
            currentDescriptionDiv.innerHTML = newDescriptionContent
            document.getElementById('editDescriptionBtn').classList.remove('d-none')
            $('#beforeProcessing').addClass('d-none')
        },
        error: function() {
            alert('Failed to save description.')
        }
    })
}

const dismissEdit = () => {
    currentDescriptionDiv.innerHTML = originalDescriptionContent
    document.getElementById('editDescriptionBtn').classList.remove('d-none')
    $('#dismissDescriptionEditConfirmModal').modal('hide')
}

var changeEliminationType = (element) => {
    let parent = $(element).parent();
    parent.find('.form-text').addClass('d-none');
    $('.elimination-type-hint').removeClass('d-none');

    if ($(element).val() == 1) {
        parent.find('.single-type-hint').removeClass('d-none');
    } else {
        parent.find('.double-type-hint').removeClass('d-none');
    }
}

let toggleVisibility = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('.visibility-hint').removeClass('d-none');
    } else {
        $('.visibility-hint').addClass('d-none');
    }
}

let toggleAvailability = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('.availability-option').removeClass('d-none');
        $('.startAv').attr('disabled', false);
        $('.endAv').attr('disabled', false);
    } else {
        $('.availability-option').addClass('d-none');
        $('.startAv').attr('disabled', true);
        $('.endAv').attr('disabled', true);
    }
}