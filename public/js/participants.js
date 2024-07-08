let shufflingPromise = null;

function callShuffle(enableShuffling = true) {
    const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)

    exampleTeams = [];
    if (enableShuffling) {
        // Use a promise to coordinate the shuffling and displaying of the message
        shufflingPromise = new Promise(resolve => {
            const startTime = new Date();

            function runFlipFuncSequentially(currentTime) {
                if ((currentTime - startTime) < shuffle_duration * 1000) {
                    setTimeout(function () {
                        shuffleList(() => {
                            runFlipFuncSequentially(new Date());
                        });
                    }, delayBetweenRuns);
                } else {
                    // Resolve the promise when all shuffling iterations are complete
                    resolve();
                }
            }

            runFlipFuncSequentially(new Date());
        });

        shufflingPromise.then(() => {
            Array.from(itemList.children).forEach((item, i) => {
                exampleTeams.push({ 'id': item.id, 'name': item.lastChild.textContent, 'order': i });
            });

            generateBrackets(exampleTeams);
        },
            function (error) { myDisplayer(error); }
        );
    } else {
        let children = Array.from(itemList.children);
        // Shuffle elements
        children = shuffleArray(Array.from(itemList.children));

        Array.from(children).forEach((item, i) => {
            exampleTeams.push({ 'id': item.id, 'name': item.lastChild.textContent, 'order': i });
        });
        generateBrackets(exampleTeams)
    }
    
}

function skipShuffling() {
    shuffle_duration = 0;
}

function shuffleList(callback) {
    const itemList = document.getElementById('newList');

    let children = Array.from(itemList.children);

    const keys = {}; // Reset keys object for each click

    // Store item elements' id and boundingClientRect
    children.forEach(elm => {
        keys[elm.id] = elm.getBoundingClientRect();
    });

    // Shuffle elements
    children = shuffleArray(Array.from(itemList.children));
    children.forEach(elm => {
        itemList.appendChild(elm);
    });

    // Apply animations
    Array.from(itemList.children).forEach(elm => {
        const first = keys[elm.id];
        const last = elm.getBoundingClientRect();

        const delta = {
            x: first.left - last.left,
            y: first.top - last.top,
        };

        gsap.set(elm, { x: delta.x, y: delta.y }); // Set initial position

        gsap.fromTo(elm, {
            x: delta.x,
            y: delta.y,
        }, {
            x: 0,
            y: 0,
            duration: 0.5,
            ease: 'ease-in-out',
            onComplete: function () {
                gsap.set(elm, { clearProps: 'all' }); // Reset properties after animation completes
            }
        });
    });


    // Execute the callback after shuffling
    if (callback && typeof callback === 'function') {
        callback();
    }

}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

/**
 * Render the list of Participants
 */
function renderParticipants(participantsArray) {
    itemList.innerHTML = '';

    let indexList = document.getElementById('indexList')
    indexList.innerHTML = '';

    if (!participantsArray.length) {
        $('.empty-message-wrapper').removeClass('d-none')
        return false
    }

    $('.empty-message-wrapper').addClass('d-none')
    participantsArray.forEach((participant, i) => {
        var item = document.createElement('div');
        item.setAttribute('id', participant.id);
        item.setAttribute('class', "list-group-item");
        item.setAttribute('data-id', participant.id);
        item.innerHTML = `<span class="p-name col-10 text-center">` + participant.name + '</span>';

        if (itemList.length > 0)
            itemList.insertBefore(item);
        else
            itemList.appendChild(item);

        var indexItem = document.createElement('div');
        indexItem.setAttribute('class', "list-group-item border-0 text-end");
        indexItem.innerHTML = `<span>${i + 1}</span>`;

        if (indexList.length > 0)
            indexList.insertBefore(indexItem);
        else
            indexList.appendChild(indexItem);
    });

    $('#newList').contextMenu({
        selector: '.list-group-item',
        items: {
            edit: {
                name: "Edit",
                callback: (key, opt, e) => {
                    var element_id = opt.$trigger.data('id');
                    const nameBox = document.createElement('input');
                    const name = opt.$trigger.children().last().text();
                    nameBox.classList.add('name-input', 'form-control');
                    nameBox.value = name;

                    const inputBox = document.createElement('div');
                    inputBox.appendChild(nameBox);
                    inputBox.classList.add('col-auto');

                    const buttonBox = document.createElement('div');
                    const button = document.createElement('button');
                    button.setAttribute('onClick', `saveParticipant(event, ${element_id})`);
                    button.classList.add('btn', 'btn-primary');
                    button.textContent = "Save";
                    buttonBox.appendChild(button);
                    buttonBox.classList.add('col-auto');

                    const html = document.createElement('div');
                    html.appendChild(inputBox);
                    html.appendChild(buttonBox);
                    html.classList.add('row', 'g-3', 'align-items-center');

                    opt.$trigger.children().last().html(html);
                }
            },
            delete: {
                name: "Delete",
                callback: (key, opt, e) => {
                    var element_id = opt.$trigger.data('id');
                    $.ajax({
                        type: "DELETE",
                        url: apiURL + '/participants/delete/' + element_id,
                        success: function (result) {
                            document.getElementById(element_id).remove();
                            $('#indexList').children().last().remove();
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    }).done(() => {
                        setTimeout(function () {
                            $("#overlay").fadeOut(300);
                        }, 500);
                    });
                }
            }
        }
    });
}

/**
 * Initialize the list of Participants
 */
function loadParticipants() {
    $("#overlay").fadeIn(300);

    $.ajax({
        type: "GET",
        url: apiURL + '/participants',
        dataType: "JSON",
        success: function (result) {
            renderParticipants(result);
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function saveParticipant(e, element_id) {
    const name = $(e.target).parents('.list-group-item').find('.name-input').val();

    $.ajax({
        type: "POST",
        url: apiURL + '/participants/update/' + element_id,
        data: { 'name': name },
        success: function (result) {
            result = JSON.parse(result);
            $(e.target).parents('.list-group-item').children().last().html(result.data.name);
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function generateBrackets(list) {
    $.ajax({
        type: "post",
        url: apiURL + '/brackets/generate',
        data: { 'type': eleminationType, 'tournament_id': tournament_id, 'list': list },
        dataType: "JSON",
        success: function (result) {
            if (result.result == 'success') window.location.href = '/tournaments/' + tournament_id + '/view';
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

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

$(document).ready(function () {
    $(".music-setting .time").inputmask(
        "99:59:59",
        {
            placeholder: "00:00:00",
            insertMode: false,
            showMaskOnHover: false,
            definitions: {
                '5': {
                    validator: "[0-5]",
                    cardinality: 1
                }
            }
        });
});


