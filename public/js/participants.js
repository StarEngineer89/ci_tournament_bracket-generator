let shufflingPromise = null;

function callShuffle(enableShuffling = true) {
    const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)
    $('#generateProcessing').removeClass('d-none')

    exampleTeams = [];
    
    // Use a promise to coordinate the shuffling and displaying of the message
    shufflingPromise = new Promise(resolve => {
        const startTime = new Date();

        function runFlipFuncSequentially(currentTime) {
            if ((currentTime - startTime) < shuffle_duration * 1000) {
                if (enableShuffling) {
                    setTimeout(function () {
                        shuffleList(() => {
                            runFlipFuncSequentially(new Date());
                        });
                    }, delayBetweenRuns);
                } else {
                    setTimeout(function () {
                        runFlipFuncSequentially(new Date());
                    }, delayBetweenRuns)
                }
            } else {
                // Resolve the promise when all shuffling iterations are complete
                resolve();
            }
        }

        runFlipFuncSequentially(new Date());
    });

    shufflingPromise.then(() => {
        Array.from(itemList.children).forEach((item, i) => {
            let img = '';
            if($(item).find('img').length > 0) img = $(item).find('img').attr('src');
            exampleTeams.push({ 'id': item.id, 'name': item.lastChild.textContent, 'image': img, 'order': i });
        });

        generateBrackets(exampleTeams);
    },
        function (error) { myDisplayer(error); }
    );
}

function skipShuffling() {
    audio.pause();
    document.getElementById('stopMusicButton').textContent = "Resume Music"
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

    let indexList = document.getElementById('indexList')

    itemList.innerHTML = ''
    indexList.innerHTML = ''

    if (participantsArray.length) {
        $('.empty-message-wrapper').addClass('d-none')
    } else {
        $('.empty-message-wrapper').removeClass('d-none')
        return false
    }

    $('.empty-message-wrapper').addClass('d-none')
    participantsArray.forEach((participant, i) => {
        var item = document.createElement('div');
        item.setAttribute('id', participant.id);
        item.setAttribute('class', "list-group-item");
        item.setAttribute('data-id', participant.id);
        let item_html = `<span class="p-name col text-center">` + participant.name + '</span>';
        if(participant.image) {
            item_html = `<img src="${participant.image}" class="p-image col-auto" height="30px" id="pimage_${participant.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-alert col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button>` + item_html;
        }else{
            item_html = `<img src="/images/avatar.jpg" class="p-image temp col-auto" id="pimage_${participant.id}" onClick="chooseImage(event, ${participant.id})" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-alert d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button>` + item_html;
        }
        item.innerHTML = item_html;

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
                    inputBox.classList.add('col');

                    const buttonBox = document.createElement('div');
                    const button = document.createElement('button');
                    button.setAttribute('onClick', `saveParticipant(event, ${element_id})`);
                    button.classList.add('btn', 'btn-primary');
                    button.textContent = "Save";
                    buttonBox.appendChild(button);
                    buttonBox.classList.add('col-auto');

                    const html = document.createElement('div');
                    //html.innerHTML = `<input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this)" name="image_${element_id}" id="image_${element_id}"/><button class="btn btn-success col-auto" onClick="chooseImage(event, ${element_id})"><i class="fa fa-upload"></i></button>`;
                    html.appendChild(inputBox);
                    html.appendChild(buttonBox);
                    html.classList.add('row', 'g-3', 'align-items-center');

                    opt.$trigger.html(html);
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
    
    if (!tournament_id) {
        renderParticipants([]);
        return false;
    }

    $.ajax({
        type: "GET",
        url: apiURL + '/tournaments/' + tournament_id + '/get-participants',
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

function chooseImage(e, element_id){
    $("#image_" + element_id).trigger('click');
}
function checkBig(el, element_id){
    if(el.files[0].size > 1048576){
        alert('Max image size is 1MB. Please upload small image.');
        this.value='';
    }else{
        var formData = new FormData();
        formData.append('image', $("#image_" + element_id)[0].files[0]);

        $.ajax({
            type: "POST",
            url: apiURL + '/participants/update/' + element_id,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (result) {
                result = JSON.parse(result);
                $("#pimage_"+element_id).attr('src', result.data.image);
                $("#pimage_"+element_id + ' ~ .btn').removeClass('d-none');
                $("#pimage_"+element_id).removeClass('temp');
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
function removeImage(e, element_id){
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/update/' + element_id,
        data: {'action': 'removeImage'},
        success: function (result) {
            result = JSON.parse(result);
            $("#pimage_"+element_id).attr('src', '/images/avatar.jpg');
            $("#pimage_"+element_id + ' ~ .btn').addClass('d-none');
            $("#pimage_"+element_id).removeClass('temp');
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
    var formData = new FormData();
    formData.append('name', name);
    formData.append('image', $("#image_" + element_id)[0].files[0]);
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/update/' + element_id,
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function (result) {
            result = JSON.parse(result);
            let participant = `<span class="p-name col text-center">${result.data.name}</span>`;
            if(result.data.image){
                participant = `<img src="${result.data.image}" class="p-image col-auto" height="30px" id="pimage_${participant.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-alert col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button>` + participant;
            }else{
                participant = `<img src="/images/avatar.jpg" class="p-image temp col-auto" id="pimage_${participant.id}" onClick="chooseImage(event, ${participant.id})" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-alert d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button>` + participant;
            }
            $(e.target).parents('.list-group-item').html(participant);
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
        data: { 'type': eleminationType, 'tournament_id': tournament_id, 'user_id': user_id, 'list': list },
        dataType: "JSON",
        beforeSend: function() {
            $('#generateProcessing').addClass('d-none')
            $('#beforeProcessing').addClass('generateProcessing')
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function (result) {
            if (result.result == 'success') window.location.href = '/tournaments/' + tournament_id + '/view?mode=edit';
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

var addParticipants = (data) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/new',
        data: {
            'name': data.names,
            'user_id' : data.user_id,
            'tournament_id': data.tournament_id
        },
        dataType: "JSON",
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            if (result.count) {
                renderParticipants(result.participants);

                $('#participantNames').val(null);
                $('input.csv-import').val(null)
                $('#confirmSave').modal('hide');
                $('#collapseAddParticipant').removeClass('show');

                appendAlert('Records inserted successfully!', 'success');
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
}

var validateParticipantNames = (names) => {
    let exisingNames = []
    itemList.querySelectorAll('#newList .p-name').forEach((item, i) => {
        exisingNames.push(item.textContent.trim())
    })

    let validNames = []
    let duplicates = []
    names.forEach(name => {
        const normalizedValue = name.replace(/\s+/g, '').toLowerCase();
        if (exisingNames.some(element => element.replace(/\s+/g, '').toLowerCase() === normalizedValue) || validNames.some(element => element.replace(/\s+/g, '').toLowerCase() === normalizedValue)) {
            duplicates.push(name)
        } else {
            validNames.push(name)
        }
    })

    return {'duplicates': duplicates, 'validNames': validNames}
}

var checkDuplicatedParticipants = () => {
    var items = $('#newList span.p-name')
    const names = _.map(items, (ele) => {
        return {
            'id': ele.parent.dataset.id,
            'name': ele.textContent
        }
    })

    if (!names.length) {
        return false;
    }

    let duplications = _.chain(names).groupBy('name').filter(function(v) {
        return v.length > 1
    }).flatten().uniq().value()

    if (duplications.length) {
        duplications = _.map(_.uniq(duplications, function(item) {
            return item.name;
        }), function(item) {
            return item.name
        })

        return duplications
    } else {
        return false
    }
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


