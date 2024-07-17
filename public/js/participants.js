let shufflingPromise = null;

function callShuffle(enableShuffling = true) {
    const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)
    $('#overlay').removeClass('d-none')

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
            exampleTeams.push({ 'id': item.id, 'name': item.lastChild.textContent, 'order': i });
        });

        generateBrackets(exampleTeams);
        $('#overlay').addClass('d-none')
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

var addParticipants = (data) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/new',
        data: {
            'name': data.names,
            'tournament_id': data.tournament_id
        },
        dataType: "JSON",
        success: function(result) {

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
        if (exisingNames.includes(name) || validNames.includes(name)) {
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


