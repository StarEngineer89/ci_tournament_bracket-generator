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
function renderParticipants(participantsData) {
    itemList.innerHTML = ''
    let enableBtn = document.querySelector('.list-tool-bar .enableBtn').cloneNode(true)
    document.querySelector('.list-tool-bar').innerHTML = ''

    let participantsArray = participantsData.participants

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
        item.setAttribute('class', "participant list-group-item d-flex");
        item.setAttribute('data-id', participant.id);
        item.setAttribute('data-name', participant.name);
        let item_html = `<span class="p-name ms-3">` + participant.name + '</span>';
        if(participant.image) {
            item_html = `<div class="p-image"><img src="${participant.image}" class="col-auto" height="30px" id="pimage_${participant.id}" data-pid="${participant.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }else{
            item_html = `<div class="p-image"><img src="/images/avatar.jpg" class="temp col-auto" id="pimage_${participant.id}" data-pid="${participant.id}" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }
        item_html += '<button class="btn btn-light bg-transparent ms-auto border-0 p-0" data-bs-toggle="tooltip" data-bs-title="Individual Participant"><svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="8" cy="6" r="3.25"></circle> <path d="m2.75 14.25c0-2.5 2-5 5.25-5s5.25 2.5 5.25 5"></path> </g></svg></button>'

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
                groupLabel.setAttribute('class', "group-name list-group-item d-flex align-items-center ps-3 border-bottom")
                groupLabel.innerHTML = `<img src="${participant.group_image}" class="group-image pe-2"><span class="name me-auto">${participant.group_name}</span>`
                groupLabel.innerHTML += '<button class="btn btn-light bg-transparent ms-auto border-0 p-0" data-bs-toggle="tooltip" data-bs-title="Group"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="8" cy="8" r="2.5" stroke="#222222" stroke-linecap="round"></circle> <path d="M11.7679 8.5C12.0332 8.04063 12.47 7.70543 12.9824 7.56815C13.4947 7.43086 14.0406 7.50273 14.5 7.76795C14.9594 8.03317 15.2946 8.47 15.4319 8.98236C15.5691 9.49472 15.4973 10.0406 15.2321 10.5C14.9668 10.9594 14.53 11.2946 14.0176 11.4319C13.5053 11.5691 12.9594 11.4973 12.5 11.2321C12.0406 10.9668 11.7054 10.53 11.5681 10.0176C11.4309 9.50528 11.5027 8.95937 11.7679 8.5L11.7679 8.5Z" stroke="#222222"></path> <path d="M13.4054 17.507L13.8992 17.4282L13.4054 17.507ZM12.5 18H3.50002V19H12.5V18ZM3.08839 17.5857C3.21821 16.7717 3.53039 15.6148 4.26396 14.671C4.97934 13.7507 6.11871 13 8.00002 13V12C5.80109 12 4.37371 12.9004 3.47442 14.0573C2.59334 15.1909 2.24293 16.5374 2.10087 17.4282L3.08839 17.5857ZM8.00002 13C9.88133 13 11.0207 13.7507 11.7361 14.671C12.4697 15.6148 12.7818 16.7717 12.9117 17.5857L13.8992 17.4282C13.7571 16.5374 13.4067 15.1909 12.5256 14.0573C11.6263 12.9004 10.199 12 8.00002 12V13ZM3.50002 18C3.20827 18 3.05697 17.7827 3.08839 17.5857L2.10087 17.4282C1.95832 18.322 2.6872 19 3.50002 19V18ZM12.5 19C13.3128 19 14.0417 18.322 13.8992 17.4282L12.9117 17.5857C12.9431 17.7827 12.7918 18 12.5 18V19Z" fill="#222222"></path> <path d="M17.2966 17.4162L16.8116 17.5377L17.2966 17.4162ZM11.8004 13.9808L11.5324 13.5586L11.0173 13.8855L11.4391 14.3264L11.8004 13.9808ZM13.4054 17.507L13.8992 17.4282L13.4054 17.507ZM16.3951 18H12.5V19H16.3951V18ZM16.8116 17.5377C16.8654 17.7526 16.7076 18 16.3951 18V19C17.2658 19 18.0152 18.2277 17.7816 17.2948L16.8116 17.5377ZM13.5001 14C14.5278 14 15.2496 14.5027 15.7784 15.2069C16.3178 15.9253 16.6345 16.8306 16.8116 17.5377L17.7816 17.2948C17.5905 16.5315 17.2329 15.4787 16.5781 14.6065C15.9126 13.7203 14.9202 13 13.5001 13V14ZM12.0683 14.4029C12.4581 14.1556 12.9262 14 13.5001 14V13C12.732 13 12.0787 13.2119 11.5324 13.5586L12.0683 14.4029ZM11.4391 14.3264C12.3863 15.3166 12.7647 16.6646 12.9116 17.5857L13.8992 17.4282C13.7397 16.4285 13.3158 14.8416 12.1617 13.6351L11.4391 14.3264ZM12.9116 17.5857C12.9431 17.7827 12.7918 18 12.5 18V19C13.3128 19 14.0417 18.322 13.8992 17.4282L12.9116 17.5857Z" fill="#222222"></path> <rect x="16.25" y="5.25" width="4.5" height="0.5" rx="0.25" stroke="#222222" stroke-width="0.5" stroke-linecap="round"></rect> <rect x="18.75" y="3.25" width="4.5" height="0.5" rx="0.25" transform="rotate(90 18.75 3.25)" stroke="#222222" stroke-width="0.5" stroke-linecap="round"></rect> </g></svg></button>'

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
        selector: '.group-name',
        build: function ($triggerElement, e) {
            let items = {}
            const reused = participantsData.reusedGroups.includes($triggerElement.parent().data('id'))

            items.edit = {
                name: "Edit Group",
                disabled: reused,
                callback: (key, opt, e) => {
                    enableGroupEdit(opt.$trigger)
                }
            }
            items.delete = {
                name: "Delete Group",
                disabled: reused,
                callback: (key, opt, e) => {
                    deleteGroup(opt.$trigger)
                }
            }
            items.ungroup = {
                name: "Ungroup",
                callback: (key, opt, e) => {
                    ungroup(opt.$trigger)
                }
            }

            return {
                items: items
            }
        }
    })

    $('#newList').contextMenu({
        selector: '.participant',
        build: function ($triggerElement, e) {
            let items = {}
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

            return {
                items: items
            }
        }
    });
    
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

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

    $('#errorModal .modal-footer button.force').remove()

    if (!document.querySelector('#input_group_name input').value && document.querySelector('#select_group select').getAttribute('disabled')) {
        document.querySelector('#errorModal .errorDetails').innerHTML = 'Please input the Group Name or select the existing group'
        $('#errorModal').modal('show')

        return false
    }

    if (!forceInsert) {
        [...document.querySelectorAll('#select_group option'), ...document.querySelectorAll('#newList .p-name')].forEach(optionEl => {
            if (!isValidate) {
                return false
            }

            if (document.querySelector('#input_group_name input').value == optionEl.textContent) {
                const includeBtn = document.createElement('button')
                includeBtn.setAttribute('class', "btn btn-primary force")
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
                renderParticipants(result)
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

let updateGroup = (e, forceUpdate = false) => {
    e.preventDefault()
    
    let isValidate = true

    $('#errorModal .modal-footer button.force').remove()

    if (!forceUpdate) {
        [...document.querySelectorAll('#newList .name'), ...document.querySelectorAll('#newList .p-name')].forEach(optionEl => {
            if (!isValidate) {
                return false
            }

            if (document.querySelector('.new-group-name').value == optionEl.textContent) {
                const includeBtn = document.createElement('button')
                includeBtn.setAttribute('class', "btn btn-primary force")
                includeBtn.textContent = "Save duplicated name"
                includeBtn.addEventListener('click', () => {
                    updateGroup(e, true)
                })
                $('#errorModal .modal-footer').prepend(includeBtn)
                $('#errorModal .errorDetails').html(`The group name "${document.querySelector('.new-group-name').value}" appears to be duplicated.`)
                $('#errorModal').modal('show')

                isValidate = false

                return false
            }
        })

        if (!isValidate) {
            return false
        }
    }
    
    if (forceUpdate) {
        $('#errorModal').modal('hide')
    }

    const data = {'group_id': e.target.parentElement.parentElement.parentElement.dataset.id, 'group_name': document.querySelector('.new-group-name').value, 'image_path': e.target.parentElement.parentElement.querySelector('.group-image').src, 'hash': hash}

    if (tournament) {
        data['tournament_id'] = tournament.id
    } else {
        data['tournament_id'] = 0
    }

    $.ajax({
        url: apiURL + '/groups/save',
        type: "POST",
        data: data,
        beforeSend: function () {
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function (result) {
            if (result.status == 'success') {
                renderParticipants(result)
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
        formData.append('image', el.files[0]);
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

                el.parentElement.querySelector('img').src = result.file_path
                if (el.parentElement.querySelector('input#group_image_path')) {
                    el.parentElement.querySelector('input#group_image_path').value = result.file_path
                }
                el.parentElement.querySelector('img').classList.remove('temp');
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
    document.getElementById('group_image').classList.add('temp')
    document.getElementById('group_image_delete').classList.add('d-none')
}

let selectGroup = (el) => {
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
    $('#confirmModal .message').html(`Are you sure to ungroup the participants from the group "${el.data('name')}"?`)
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

                renderParticipants(result)
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

let deleteGroup = (el) => {
    const group_id = el.parent().data('id')

    $('#confirmModal .message').html(`Are you sure to remove this group "${el.data('name')}"?`)
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let data = {'group_id': group_id, 'hash': hash}
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/delete',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .message').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                renderParticipants(result)
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

    $('#confirmModal .message').html(`Are you sure to remove this participant "${el.data('name')}" from the group "${el.parent().data('name')}"?`)
    $('#confirmModal .text-danger').addClass('d-none')
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

let enableGroupEdit = (el) => {
    var group_id = el.data('id');
    var originalHtml = el.html()

    const nameBox = document.createElement('input');
    const name = el.parent().find('span.name').text();
    nameBox.classList.add('new-group-name', 'form-control');
    nameBox.value = name;

    const buttonWrapper = document.createElement('div');
    const saveBtn = document.createElement('button');
    saveBtn.classList.add('btn', 'btn-primary', 'ms-1');
    saveBtn.textContent = "Update";
    saveBtn.addEventListener('click', event => {
        updateGroup(event)
    });
    buttonWrapper.appendChild(saveBtn);
    buttonWrapper.classList.add('col-auto');

    const cancelBtn = document.createElement('button')
    cancelBtn.classList.add('btn', 'btn-secondary', 'ms-1')
    cancelBtn.textContent = 'Cancel'
    cancelBtn.addEventListener('click', event => {
        event.target.parentElement.parentElement.setAttribute('data-bs-toggle', "collapse")
        event.target.parentElement.parentElement.classList.add('group-name')
        event.target.parentElement.parentElement.innerHTML = originalHtml
    })
    buttonWrapper.appendChild(cancelBtn)

    const imgWrapper = document.createElement('div')
    const img = el.find('img.group-image')[0]
    const fileInput = document.createElement('input')
    fileInput.type = 'file';
    fileInput.className = 'd-none';
    imgWrapper.appendChild(img)
    imgWrapper.appendChild(fileInput)
    
    img.addEventListener('click', event => {
        fileInput.click()
    })
    fileInput.addEventListener('change', event => {
        uploadGroupImage(event.target)
    })

    el.removeClass('group-name')
    el.html('');
    el.append(imgWrapper)
    el.append(nameBox)
    el.append(buttonWrapper)
    el.removeAttr('data-bs-toggle')
}