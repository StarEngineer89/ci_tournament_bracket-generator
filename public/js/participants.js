let shufflingPromise = null;

function callShuffle(enableShuffling = true) {
    const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)

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
        Array.from(document.getElementById('newList').children).forEach((item, i) => {
            let img = '';
            let members = [];
            if ($(item).find('img').length > 0) img = $(item).find('img').attr('src');
            if (item.dataset.isGroup) {
                Array.from(item.children[1].children).forEach((member, i) => {
                    members.push({'id': member.dataset.id, 'order': i})
                })
            }
            exampleTeams.push({ 'id': item.dataset.id, 'order': i, 'is_group': item.dataset.isGroup, 'members': members });
            console.log(item.dataset.isGroup)
        });

        generateBrackets(exampleTeams);
    },
        function (error) { myDisplayer(error); }
    );
}

function skipShuffling() {
    audio.pause();
    document.getElementById('stopAudioButton').textContent = "Resume Audio"
    shuffle_duration = 0;
}

function shuffleList(callback) {
    const list = document.getElementById('newList');

    let children = Array.from(list.children);

    const keys = {}; // Reset keys object for each click

    // Store item elements' id and boundingClientRect
    children.forEach(elm => {
        keys[elm.id] = elm.getBoundingClientRect();
    });

    // Shuffle elements
    children = shuffleArray(Array.from(list.children));
    children.forEach(elm => {
        document.getElementById('newList').appendChild(elm);
    });

    // Apply animations
    Array.from(list.children).forEach(elm => {
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
    itemList.innerHTML = ''
    let enableBtn = document.querySelector('.list-tool-bar .enableBtn').cloneNode(true)
    document.querySelector('.list-tool-bar').innerHTML = ''

    if (participantsArray.length) {
        $('.empty-message-wrapper').addClass('d-none')
    } else {
        $('.empty-message-wrapper').removeClass('d-none')
        return false
    }

    if (participantsArray.length > 2) {
        enableBtn.classList.remove('d-none')
        document.querySelector('.list-tool-bar').appendChild(enableBtn)
    }

    enable_confirmPopup = true;

    $('.empty-message-wrapper').addClass('d-none')

    let groups = {}

    const noteIcon = document.createElement('button')
    noteIcon.setAttribute('class', "noteBtn ms-2 btn btn-light p-0 bg-transparent border-0")
    noteIcon.innerHTML = `<i class="fa-classic fa-solid fa-circle-exclamation"></i>`
    noteIcon.setAttribute('data-bs-toggle', 'tooltip');
    noteIcon.setAttribute('data-bs-placement', 'top');
    noteIcon.setAttribute('data-bs-html', true)
    noteIcon.setAttribute('title', 'You may group individual participants together by selecting each one in the list belonging to the same group.<br/>Note: Nested grouping is not an option, meaning groups cannot be grouped within one another!');
    const tooltip = new bootstrap.Tooltip(noteIcon)
    document.querySelector('.list-tool-bar').appendChild(noteIcon)
    
    participantsArray.forEach((participant, i) => {
        var item = document.createElement('div');
        item.setAttribute('id', participant.id);
        item.setAttribute('class', "list-group-item d-flex");
        item.setAttribute('data-id', participant.id);
        item.setAttribute('data-name', participant.name);
        let item_html = `<span class="p-name ms-3">` + participant.name + '</span>';
        if(participant.image) {
            item_html = `<div class="p-image"><img src="${participant.image}" class="col-auto" height="30px" id="pimage_${participant.id}" data-pid="${participant.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }else{
            item_html = `<div class="p-image"><img src="/images/avatar.jpg" class="temp col-auto" id="pimage_${participant.id}" data-pid="${participant.id}" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }

        item.innerHTML = item_html;

        if (!participant.group_id) {
            itemList.appendChild(item)
        } else {
            if (!(participant.group_id in groups)) {
                const groupHtml = document.createElement('div')
                groupHtml.setAttribute('class', 'group')
                groupHtml.setAttribute('data-id', participant.group_id)
                groupHtml.setAttribute('data-is-group', true)

                if (!participant.group_image) participant.group_image = "/images/group-placeholder.png"
                const groupLabel = document.createElement('div')
                groupLabel.setAttribute('class', "group-name list-group-item d-flex align-items-center p-1 ps-3 border-bottom")
                groupLabel.innerHTML = `<img src="${participant.group_image}" class="group-image pe-2"><span class="name me-auto">${participant.group_name}</span>`

                groupLabel.setAttribute('data-bs-toggle', "collapse")
                groupLabel.setAttribute('data-bs-target', `#group_${participant.group_id}`)
                groupLabel.setAttribute('data-name', `${participant.group_name}`)

                const groupList = document.createElement('div')
                groupList.setAttribute('id', `group_${participant.group_id}`)
                groupList.setAttribute('class', 'list-group list-group-numbered ms-3 collapse')
                groupList.setAttribute('data-group', participant.group_id)
                groupList.setAttribute('data-name', participant.group_name)

                groupHtml.appendChild(groupLabel)
                groupHtml.appendChild(groupList)

                groups[participant.group_id] = groupHtml

                itemList.appendChild(groups[participant.group_id])
            }

            groups[participant.group_id].children[1].appendChild(item);
        }
    });

    $('#newList').contextMenu({
        selector: '.list-group-item',
        build: function ($triggerElement, e) {
            let items = {}
            if ($triggerElement.parent().hasClass('group')) {
                items.edit = {
                    name: "Edit Group",
                    callback: (key, opt, e) => {
                        
                    }
                }
                items.delete = {
                    name: "Delete",
                    callback: (key, opt, e) => {
                        
                    }
                }
                items.ungroup = {
                    name: "Ungroup",
                    callback: (key, opt, e) => {
                        ungroup(opt.$trigger)
                    }
                }
            } else {
                items.edit = {
                    name: "Edit",
                    callback: (key, opt, e) => {
                        var element_id = opt.$trigger.data('id');
                        const nameBox = document.createElement('input');
                        const name = opt.$trigger.children().last().text();
                        nameBox.classList.add('name-input', 'form-control');
                        nameBox.value = name;

                        $(nameBox).atwho({
                            at: "@",
                            searchKey: 'username',
                            data: users,
                            limit: 5, // Show only 5 suggestions
                            displayTpl: "<li data-value='@${id}'>${username}</li>",
                            insertTpl: "@${username}",
                            callbacks: {
                                remoteFilter: function (query, callback) {
                                    if (query.length < 1) return; // Don't fetch on empty query
                                    $.ajax({
                                        url: apiURL + '/tournaments/get-users', // Your API endpoint
                                        type: "GET",
                                        data: {
                                            query: query
                                        },
                                        dataType: "json",
                                        success: function (data) {
                                            callback(data);
                                        }
                                    });
                                }
                            }
                        });

                        const inputBox = document.createElement('div');
                        inputBox.appendChild(nameBox);
                        inputBox.classList.add('col');

                        const buttonBox = document.createElement('div');
                        const button = document.createElement('button');
                        button.classList.add('btn', 'btn-primary');
                        button.textContent = "Save";
                        button.setAttribute('onClick', `saveParticipant(event, ${element_id})`);
                        buttonBox.appendChild(button);
                        buttonBox.classList.add('col-auto');

                        const cancelBtn = document.createElement('button')
                        cancelBtn.classList.add('btn', 'btn-secondary', 'ms-2')
                        cancelBtn.textContent = 'Cancel'
                        cancelBtn.setAttribute('onClick', 'cancelEditing(this)')
                        buttonBox.appendChild(cancelBtn)

                        const html = document.createElement('div');
                        //html.innerHTML = `<input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this)" name="image_${element_id}" id="image_${element_id}"/><button class="btn btn-success col-auto" onClick="chooseImage(event, ${element_id})"><i class="fa fa-upload"></i></button>`;
                        html.appendChild(inputBox);
                        html.appendChild(buttonBox);
                        html.classList.add('row', 'g-3', 'align-items-center');

                        originalHtml = opt.$trigger.html()

                        opt.$trigger.html(html);
                
                        const originalObj = document.createElement('div')
                        originalObj.classList.add('original', 'd-none')
                        originalObj.innerHTML = originalHtml
                        opt.$trigger.append(originalObj)
                    }
                }
                items.delete = {
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

                if ($triggerElement.parent().data('group')) {
                    items.ungroup = {
                        name: `Remove from Group "${$triggerElement.parent().data('name')}"`,
                        callback: (key, opt, e) => {
                            removeParticipantFromGroup(opt.$trigger)
                        }
                    }
                }
            }

            return {
                items: items
            }
        }
    });

    cancelMakeGroup()
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

$(document).on("click", ".p-image img", function(){
    var pid = $(this).data('pid');
    if($(this).hasClass('temp')){
        $("#image_" + pid).trigger('click');
    }else{
        $(this).parent().addClass('active');
    }
})

$(document).on("click", "#group_image", function(){
    if($(this).hasClass('temp')){
        $("#group_image_input").trigger('click');
    } else {
        $("#group_image" + ' ~ .btn').removeClass('d-none');
    }
})

$(document).on("click", function(e){
    if(!$(e.target.parentElement).hasClass('p-image')) $(".p-image").removeClass('active');
})

function chooseImage(e, element_id){
    $("#image_" + element_id).trigger('click');
}
function checkBig(el, element_id){
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!allowedTypes.includes(el.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload image as *.jpeg, *.jpg, *.png, *.gif format.')
        $("#errorModal").modal('show');

        this.value = '';
        return
    }

    if (el.files[0].size > 3145728) {
        $('#errorModal .errorDetails').html('Max image size is 3MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        this.value='';
        return
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
                if (result.errors) {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

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
            $("#pimage_"+element_id).addClass('temp');
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

    let parentElement = $(e.target).parent().parent().parent()

    if (parentElement.data('name') == name) {
        confirm(" No changes were made")
        return false
    }

    let ability = true;
    $('.p-name').each((i, e) => {
        if (e.textContent.trim() == name) {
            let confirm_result = confirm("The same name already exists in the list. Are you sure you want to proceed?");

            if (confirm_result == false) {
                ability = false;
                return false;
            }
        }
    });

    if (ability) {
        var formData = new FormData();
        formData.append('name', name);
        // formData.append('image', $("#image_" + element_id)[0].files[0]);
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
                if (result.data.image) {
                    participant = `<div class="p-image"><img src="${result.data.image}" class="col-auto" height="30px" id="pimage_${result.data.id}" data-pid="${result.data.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${result.data.id})" name="image_${result.data.id}" id="image_${result.data.id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${result.data.id})"><i class="fa fa-trash-alt"></i></button></div>` + participant;
                } else {
                    participant = `<div class="p-image"><img src="/images/avatar.jpg" class="temp col-auto" id="pimage_${result.data.id}" data-pid="${result.data.id}" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${result.data.id})" name="image_${result.data.id}" id="image_${result.data.id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${result.data.id})"><i class="fa fa-trash-alt"></i></button></div>` + participant;
                }
                $(e.target).parents('.list-group-item').data('name', result.data.name)
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
}

function generateBrackets(list) {
    $.ajax({
        type: "post",
        url: apiURL + '/brackets/generate',
        data: { 'type': eleminationType, 'tournament_id': tournament_id, 'user_id': user_id, 'list': list },
        beforeSend: function() {
            $('#generateProcessing').removeClass('d-none')
        },
        success: function (result) {
            if (result.result == 'success') {
                window.location.href = '/tournaments/' + tournament_id + '/view' 
            } else {
                $('#errorModal .errorDetails').html(result.message)
                $("#errorModal").modal('show');

                return false
            }
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        $('#generateProcessing').addClass('d-none')
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

var cancelEditing = (element) => {
    const orignal = $(element).parents('.list-group-item').find('.original').html()
    $(element).parents('.list-group-item').html(orignal)
}

var addParticipants = (data) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/new',
        data: {
            'name': data.names,
            'user_id' : data.user_id,
            'tournament_id': data.tournament_id,
            'hash': hash
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
    document.querySelectorAll('#newList .p-name').forEach((item, i) => {
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
    $(".audio-setting .time").inputmask(
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

let enableGroupParticipants = () => {
    document.querySelectorAll('#newList > .list-group-item').forEach(element => {
        // Add checkboxs to the participant list
        const checkBoxWrapper = document.createElement('div')
        checkBoxWrapper.setAttribute('class', 'form-check ms-auto p-2')

        const checkBox = document.createElement('input')
        checkBox.setAttribute('type', "checkbox")
        checkBox.setAttribute('class', "form-check-input")
        checkBox.setAttribute('value', element.id)

        checkBoxWrapper.appendChild(checkBox)
        element.appendChild(checkBoxWrapper)

    })

    // Add the Make a Group button
    const makeGroupBtn = document.createElement('button')
    makeGroupBtn.setAttribute('class', "group-action btn btn-primary ms-auto")
    makeGroupBtn.textContent = "Save"

    const cancelBtn = document.createElement('button')
    cancelBtn.setAttribute('class', "group-action btn btn-secondary ms-2")
    cancelBtn.textContent = "Cancel"

    makeGroupBtn.addEventListener('click', makeGroup)
    cancelBtn.addEventListener('click', cancelMakeGroup)

    document.querySelector('.list-tool-bar .enableBtn').classList.add('d-none')
    document.querySelector('.list-tool-bar .noteBtn').classList.add('d-none')
    document.querySelector('.list-tool-bar').appendChild(makeGroupBtn)
    document.querySelector('.list-tool-bar').appendChild(cancelBtn)
}

let makeGroup = (event) => {
    group_participants = []
    document.querySelectorAll('#newList > .list-group-item input[type="checkbox"]').forEach(element => {
        if (element.checked) {
            group_participants.push(element.value)
        }
    })

    if (group_participants.length) {
        $('#makeGroupModal').modal('show')
    } else {
        $('#selectParticipantsAlertModal').modal('show')
    }
}

let cancelMakeGroup = (event) => {
    const checkboxs = document.querySelectorAll('#newList > .list-group-item input[type="checkbox"]');
    checkboxs.forEach(ckb => ckb.remove());

    const buttons = document.querySelectorAll('.list-tool-bar .btn.group-action');
    buttons.forEach(btn => btn.remove());
    document.querySelector('.list-tool-bar .enableBtn').classList.remove('d-none')
    document.querySelector('.list-tool-bar .noteBtn').classList.remove('d-none')
}

let drawGroupsInModal = () => {
    $.ajax({
        url: apiURL + '/groups/get-list',
        type: "get",
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            if (result.status == 'success' && result.groups.length) {
                document.querySelector('#makeGroupModal #select_group select').innerHTML = ''
                result.groups.forEach(group => {
                    let option = document.createElement('option')
                    option.setAttribute('value', group.id)
                    option.setAttribute('data-image', group.image_path)
                    option.textContent = group.group_name
                    document.querySelector('#makeGroupModal #select_group select').appendChild(option)
                })

                chooseGroupType(document.querySelector('#makeGroupModal #create_new_group'))
            }
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

let chooseGroupType = (element) => {
    if (element.value == 'new') {
        document.querySelector('#makeGroupModal #input_group_name').classList.remove('d-none')
        document.querySelector('#makeGroupModal #input_group_name input').removeAttribute('disabled')
        document.querySelector('#makeGroupModal #select_group').classList.add('d-none')
        document.querySelector('#makeGroupModal #select_group select').setAttribute('disabled', true)
        document.querySelector('#makeGroupModal .group-image img').setAttribute('src', '/images/group-placeholder.png')
    }

    if (element.value == 'reuse') {
        document.querySelector('#makeGroupModal #input_group_name').classList.add('d-none')
        document.querySelector('#makeGroupModal #input_group_name input').setAttribute('disabled', true)
        document.querySelector('#makeGroupModal #select_group').classList.remove('d-none')
        document.querySelector('#makeGroupModal #select_group select').removeAttribute('disabled')
        let selectedOption = document.querySelector('#makeGroupModal #select_group select option:checked')
        document.querySelector('#makeGroupModal .group-image img').setAttribute('src', selectedOption.getAttribute('data-image'))
    }
}

let saveGroup = (e, forceInsert = false) => {
    e.preventDefault()
    
    let isValidate = true

    if (!document.querySelector('#input_group_name input').value && !document.querySelector('#select_group select').value) {
        document.querySelector('#errorModal .message').innerHTML = 'Please input the Group Name or select the existing group'
        $('#errorModal').modal('show')

        return false
    }

    if (!forceInsert) {
        [...document.querySelectorAll('#select_group option'), ...document.querySelectorAll('#newList .p-name')].forEach(optionEl => {
            if (document.querySelector('#input_group_name input').value == optionEl.textContent) {
                const includeBtn = document.createElement('button')
                includeBtn.setAttribute('class', "btn btn-primary")
                includeBtn.textContent = "Save duplicated name"
                includeBtn.addEventListener('click', () => {
                    saveGroup(e, true)
                })
                $('#errorModal .modal-footer').prepend(includeBtn)
                $('#errorModal .errorDetails').html(`The group name "${document.querySelector('#input_group_name input').value}" appears to be duplicated.`)
                $('#errorModal').modal('show')

                isValidate = false

                return false
            }
        })

        if (!isValidate) {
            return false
        }
    }
    
    if (forceInsert) {
        $('#errorModal').modal('hide')
    }

    const data = Object.fromEntries($('#create_group_form').serializeArray().map(({
        name,
        value
    }) => [name, value]));

    data['hash'] = hash

    if (group_participants.length) {
        data['participants'] = group_participants
    } else {
        return false
    }

    if (tournament) {
        data['tournament_id'] = tournament.id
    }

    $.ajax({
        url: apiURL + '/groups/save',
        type: "POST",
        data: data,
        beforeSend: function () {
            //$("#preview").fadeOut();
            $('#create_group_form').modal('hide');
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function (result) {
            if (result.status == 'success') {
                $('#makeGroupModal').modal('hide')
                renderParticipants(result.participants)
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    }).done(() => {
        setTimeout(function () {
            $("#beforeProcessing").fadeOut(300);
        }, 500)
    });
}

let uploadGroupImage = (el) => {
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!allowedTypes.includes(el.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload image as *.jpeg, *.jpg, *.png, *.gif format.')
        $("#errorModal").modal('show');

        this.value = '';
        return
    }

    if (el.files[0].size > 3145728) {
        $('#errorModal .errorDetails').html('Max image size is 3MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        this.value='';
        return
    }else{
        var formData = new FormData();
        formData.append('image', $("#group_image_input")[0].files[0]);
        formData.append('type', "group");

        $.ajax({
            type: "POST",
            url: apiURL + '/upload-image',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

                $("#group_image").attr('src', result.file_path);
                $('#group_image_path').val(result.file_path)
                $("#group_image").removeClass('temp');
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

let removeGroupImage = (e, element_id) => {
    document.getElementById('group_image_input').value = ''
    document.getElementById('group_image').src = '/images/group-placeholder.png'
    document.getElementById('group_image_delete').classList.add('d-none')
}

let changeGroup = (el) => {
    const selectedOption = el.options[el.selectedIndex];
    if (selectedOption.dataset.image && selectedOption.dataset.image != 'null') {
        document.getElementById('group_image').src = selectedOption.dataset.image
        document.getElementById('group_image_path').value = selectedOption.dataset.image
    } else {
        document.getElementById('group_image').src = '/images/group-placeholder.png'
        document.getElementById('group_image_path').value = null
    }
}

let ungroup = (el) => {
    let group_id = el.parent().data('id')
    $('#confirmModal .message').html(`Are you sure to ungroup the participants in this group "${el.data('name')}"?`)
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let participant_ids = []
        document.querySelectorAll(`#newList .group[data-id="${group_id}"] .list-group-item`).forEach(item => {
            participant_ids.push(item.dataset.id)
        })

        let data = { 'participants': participant_ids, 'group_id': group_id, 'hash': hash }
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/reset',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .message').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                renderParticipants(result.participants)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            $('#confirmModal').modal('hide')

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let removeParticipantFromGroup = (el) => {
    const group_id = el.parent().data('group')

    $('#confirmModal .message').html('Are you sure to remove this participant from the group?')
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let data = {'participant_id': el.data('id'), 'group_id': group_id, 'hash': hash}
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/remove-participant',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .message').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                renderParticipants(result.participants)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            $('#confirmModal').modal('hide')

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let editGroup = (el) => {
    var group_id = el.data('id');
    var originalHtml = el.parent().html()

    const nameBox = document.createElement('input');
    const name = el.parent().find('span.name').text();
    nameBox.classList.add('group-name', 'form-control');
    nameBox.value = name;

    const buttonWrapper = document.createElement('div');
    const button = document.createElement('button');
    button.classList.add('btn', 'btn-primary');
    button.textContent = "Save";
    button.setAttribute('onClick', `saveParticipant(event, ${group_id})`);
    buttonWrapper.appendChild(button);
    buttonWrapper.classList.add('col-auto');

    const cancelBtn = document.createElement('button')
    cancelBtn.classList.add('btn', 'btn-secondary', 'ms-2')
    cancelBtn.textContent = 'Cancel'
    cancelBtn.setAttribute('onClick', 'cancelEditing(this)')
    buttonWrapper.appendChild(cancelBtn)

    const html = document.createElement('div');
    //html.innerHTML = `<input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this)" name="image_${element_id}" id="image_${element_id}"/><button class="btn btn-success col-auto" onClick="chooseImage(event, ${element_id})"><i class="fa fa-upload"></i></button>`;
    html.appendChild(nameBox);
    html.appendChild(buttonWrapper);

    el.parent().html(html);
}