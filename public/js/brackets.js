let bracketCount = 0,
    brackets = [];

let eleminationType = "Single";
let editing_mode = false;
var ws;

function generateUUIDByDevice() {
    const navigatorInfo = window.navigator.userAgent;  // User agent info
    const screenInfo = `${screen.height}x${screen.width}x${screen.colorDepth}`;  // Screen resolution and color depth

    // Combine device information into a single string
    const deviceInfo = navigatorInfo + screenInfo;

    // Generate a hash (simple hash) from the device info
    function hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = (hash << 5) - hash + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash;
    }

    // Convert the hash to a UUID-like format
    function formatToUUID(hash) {
        const hexString = (hash >>> 0).toString(16);
        return `${hexString.substring(0, 8)}-${hexString.substring(8, 12)}-${hexString.substring(12, 16)}-${hexString.substring(16, 20)}-${hexString.substring(20, 32)}`;
    }

    const hashedDeviceInfo = hashString(deviceInfo);
    const uuid = formatToUUID(hashedDeviceInfo);

    return uuid;
}

const UUID = generateUUIDByDevice()

console.log(generateUUIDByDevice());

$(document).on('ready', function () {
    
    $("#overlay").fadeIn(300);
    try{
        ws = new WebSocket('ws://'+location.hostname+':8089');
        ws.onopen = function(e) {
            console.log("Connection established!");
            loadBrackets();
        };

        ws.onmessage = function(e) {
            console.log(e.data);
            loadBrackets();
        };
    }catch(exception){
        alert("Websocket is not running now. The result will not be updated real time.");
        loadBrackets();
    }
    initialize();

});

    /*
     * Build our bracket "model"
     */
    function drawBrackets() {
        if (brackets.length > 0)
            renderBrackets(brackets);

        document.querySelectorAll('span.tooltip-span').forEach((element, i) => {
            var tooltip = new bootstrap.Tooltip(element)
        })
    }

    /*
     * Inject our brackets
     */
    function renderBrackets(struct) {
        var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;

        var group = $('<div class="groups group' + (groupCount + 1) + '" id="b' + bracketCount + '" style="min-width:' + 240 * groupCount + "px" + '"></div>'),
            grouped = _.groupBy(struct, function (s) { return s.roundNo; });

        // document.getElementById('brackets').style.width = 170 * (groupCount + 1) + 'px';

        for (g = 1; g <= groupCount; g++) {
            var round = $('<div class="r' + g + '"></div>');
            
            let editIcon = ''
            if (hasEditPermission) {
                editIcon = `<span class="fa fa-pencil" onclick="enableChangeRoundName(event)"></span>`
            }
            
            let roundName = $(`<div class="text-center p-2 m-1 border" style="height: auto" data-round-no="${g}"></div>`)
            let round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}`
            if (grouped[g][0].final_match && grouped[g][0].final_match !== "0") {
                round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}: Grand Final`
            }

            roundName.html(`<span class="round-name">${round_name}</span> ${editIcon}`)
            round.append(roundName)

            var bracketBoxList = $('<div class="bracketbox-list"></div>')

            _.each(grouped[g], function (gg) {
                var obj = document.createElement('span');
                obj.dataset.order = gg.bracketNo;
                obj.dataset.bracket = gg.id;
                obj.dataset.next = gg.nextGame;
                obj.dataset.round = gg.roundNo;
                obj.textContent = ' ';

                var scoreBox = document.createElement('span')
                scoreBox.classList.add('score')

                var votesBox = document.createElement('span')
                votesBox.classList.add('votes')

                var voteBtnTemplate = document.createElement('button')
                voteBtnTemplate.classList.add('vote-btn')
                var voteBtnIcon = document.createElement('span')
                voteBtnIcon.classList.add('fa')
                voteBtnIcon.classList.add('fa-plus')
                voteBtnTemplate.appendChild(voteBtnIcon)

                var pidBox = document.createElement('span')
                pidBox.classList.add('p-id')

                var teams = JSON.parse(gg.teamnames);
                var teama = obj.cloneNode(true);
                teama.className = 'bracket-team teama';
                if (teams[0] != undefined) {
                    var pid = pidBox.cloneNode(true)
                    pid.textContent = parseInt(teams[0].order) + 1
                    teama.appendChild(pid)
                    
                    if(teams[0].image){
                        $(teama).append(`<div class="p-image"><img src="${teams[0].image}" height="30px" width="30px" class="object-cover" id="pimage_${teams[0].id}" data-pid="${teams[0].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[0].id})" name="image_${teams[0].id}" id="image_${teams[0].id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${teams[0].id})"><i class="fa fa-trash-alt"></i></button></div>`);
                    }else{
                        $(teama).append(`<div class="p-image"><img src="/images/avatar.jpg" height="30px" width="30px" class="temp object-cover" id="pimage_${teams[0].id}" data-pid="${teams[0].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[0].id})" name="image_${teams[0].id}" id="image_${teams[0].id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${teams[0].id})"><i class="fa fa-trash-alt"></i></button></div>`)
                    }

                    teama.dataset.id = teams[0].id;
                    teama.dataset.p_order = teams[0].order;
                    var nameSpan = document.createElement('span')
                    nameSpan.classList.add('name')
                    nameSpan.classList.add('tooltip-span')
                    nameSpan.setAttribute('data-bs-toggle', "tooltip")
                    nameSpan.setAttribute('data-bs-title', teams[0].name)
                    nameSpan.textContent = teams[0].name;
                    teama.appendChild(nameSpan)

                    if (teams[0].id == gg.winner) {
                        teama.classList.add('winner');
                    }

                    var wrapper = document.createElement('span')
                    wrapper.classList.add('score-wrapper')
                    wrapper.classList.add('d-flex')

                    if (isScoreEnabled) {
                        var score = scoreBox.cloneNode(true)
                        var scorePoint = 0
                        if (incrementScoreType == 'p') {
                            for (round_i = 0; round_i < g - 1; round_i++) {
                                scorePoint += scoreBracket
                                scorePoint += incrementScore * round_i
                            }

                            if (teams[0].id == gg.winner) {
                                scorePoint += scoreBracket
                                scorePoint += incrementScore * (g - 1)
                            }
                        } else {
                            scorePoint += scoreBracket
                            if (g == 1 && teams[0].id !== gg.winner) {
                                scorePoint = 0
                            }

                            for (round_i = 0; round_i < g - 2; round_i++) {
                                scorePoint += scorePoint * incrementScore 
                            }
                            
                            if (g > 1 && teams[0].id == gg.winner) {
                                scorePoint += scorePoint * incrementScore
                            }
                        }
                    
                        score.textContent = scorePoint
                        wrapper.appendChild(score)
                    }
                    
                    if (votingEnabled && parseInt(gg.final_match) == 0) {
                        var votes = votesBox.cloneNode(true)
                        votes.textContent = teams[0].votes ? teams[0].votes : 0
                        // Set up the tooltip with HTML content (a button)
                        wrapper.appendChild(votes)

                        // Check if vote history is existing
                        let storage_key = 'vote_t' + tournament_id + '_n' + gg.roundNo + '_b' + gg.bracketNo
                        let vp_id = window.localStorage.getItem(storage_key)
                        if (vp_id && vp_id == teams[0].id) {
                            teams[0].voted = true
                        }

                        if (!parseInt(gg.win_by_host) && !teams[0].voted && ([votingMechanismRoundDurationCode, votingMechanismOpenEndCode].includes(votingMechanism) || (maxVoteCount > 0 && teams[0].votes_in_round < maxVoteCount))) {
                            var voteBtn = voteBtnTemplate.cloneNode(true)
                            voteBtn.dataset.id = teama.dataset.id
                            voteBtn.addEventListener('click', (event) => {
                                submitVote(event)
                            })
                            wrapper.appendChild(voteBtn)
                        }
                    }

                    teama.appendChild(wrapper)
                }

                var teamb = obj.cloneNode(true);
                teamb.className = 'bracket-team teamb';
                if (teams[1] != undefined) {
                    var pid = pidBox.cloneNode(true)
                    pid.textContent = parseInt(teams[1].order) + 1
                    teamb.appendChild(pid)

                    if(teams[1].image){
                        $(teamb).append(`<div class="p-image"><img src="${teams[1].image}" height="30px" width="30px" class="object-cover" id="pimage_${teams[1].id}" data-pid="${teams[1].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[1].id})" name="image_${teams[1].id}" id="image_${teams[1].id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${teams[1].id})"><i class="fa fa-trash-alt"></i></button></div>`);
                    }else{
                        $(teamb).append(`<div class="p-image"><img src="/images/avatar.jpg" height="30px" width="30px" class="temp object-cover" id="pimage_${teams[1].id}" data-pid="${teams[1].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[1].id})" name="image_${teams[1].id}" id="image_${teams[1].id}"/><button class="btn btn-danger col-auto" onClick="removeImage(event, ${teams[1].id})"><i class="fa fa-trash-alt"></i></button></div>`)
                    }

                    teamb.dataset.id = teams[1].id;
                    teamb.dataset.p_order = teams[1].order;
                    var nameSpan = document.createElement('span')
                    nameSpan.classList.add('name')
                    nameSpan.classList.add('tooltip-span')
                    nameSpan.setAttribute('data-bs-toggle', "tooltip")
                    nameSpan.setAttribute('data-bs-title', teams[1].name)
                    nameSpan.textContent = teams[1].name
                    teamb.appendChild(nameSpan)

                    if (teams[1].id == gg.winner) {
                        teamb.classList.add('winner');
                    }

                    var wrapper = document.createElement('span')
                    wrapper.classList.add('score-wrapper')
                    wrapper.classList.add('d-flex')

                    if (isScoreEnabled) {
                        var score = scoreBox.cloneNode(true)
                        var scorePoint = 0
                        if (incrementScoreType == 'p') {
                            for (round_i = 0; round_i < g - 1; round_i++) {
                                scorePoint += scoreBracket
                                scorePoint += incrementScore * round_i
                            }

                            if (teams[1].id == gg.winner) {
                                scorePoint += scoreBracket
                                scorePoint += incrementScore * (g - 1)
                            }
                        } else {
                            scorePoint += scoreBracket
                            if (g == 1 && teams[1].id !== gg.winner) {
                                scorePoint = 0
                            }

                            for (round_i = 0; round_i < g - 2; round_i++) {
                                scorePoint += scorePoint * incrementScore
                            }
                            
                            if (g > 1 && teams[1].id == gg.winner) {
                                scorePoint += scorePoint * incrementScore
                            }
                        }
                    
                        score.textContent = scorePoint
                        wrapper.appendChild(score)
                    }

                    if (votingEnabled) {
                        var votes = votesBox.cloneNode(true)
                        votes.textContent = teams[1].votes ? teams[1].votes : 0
                        // Set up the tooltip with HTML content (a button)
                        wrapper.appendChild(votes)

                        // Check if vote history is existing
                        let storage_key = 'vote_t' + tournament_id + '_n' + gg.roundNo + '_b' + gg.bracketNo
                        let vp_id = window.localStorage.getItem(storage_key)
                        if (vp_id && vp_id == teams[1].id) {
                            teams[1].voted = true
                        }

                        if (!parseInt(gg.win_by_host) && !teams[1].voted && ([votingMechanismRoundDurationCode, votingMechanismOpenEndCode].includes(votingMechanism) || (maxVoteCount > 0 && teams[1].votes_in_round < maxVoteCount))) {
                            // Add "Vote" button
                            var voteBtn = voteBtnTemplate.cloneNode(true)
                            voteBtn.dataset.id = teamb.dataset.id
                            voteBtn.addEventListener('click', (event) => {
                                submitVote(event)
                            })
                            wrapper.appendChild(voteBtn)
                        }
                    }

                    teamb.appendChild(wrapper)
                }

                var bracket = document.createElement('div')

                if (parseInt(gg.final_match)) {
                    bracket.className = "bracketbox final";
                    teama.className = (teams[0]) ? "bracket-team teama winner" : 'bracket-team teama';
                } else {
                    var bracketNo = document.createElement('span')
                    bracketNo.classList.add('bracketNo')
                    bracketNo.innerHTML = gg.bracketNo
                    bracket.append(bracketNo)
                    bracket.className = "bracketbox";
                }

                bracket.append(teama);

                if (!parseInt(gg.final_match))
                    bracket.append(teamb);

                bracketBoxList.append(bracket);
            });

            round.append(bracketBoxList)

            group.append(round);
        }

        if (hasEditPermission) {
            $.contextMenu({
                selector: '.bracket-team',
                build: function ($triggerElement, e) {
                    let isWinner = ($triggerElement.hasClass('winner')) ? true : false;
                    let items = {}
                    if (!votingEnabled || ![votingMechanismRoundDurationCode, votingMechanismMaxVoteCode].includes(votingMechanism) || allowHostOverride) {
                        items.mark = {
                                name: (!isWinner) ? "Mark as Winner" : "Unmark as winner",
                                callback: (key, opt, e) => {
                                    if (!isWinner)
                                        markWinner(key, opt, e)
                                    else
                                        unmarkWinner(key, opt, e)
                                },
                        }
                    }

                    items.change = {
                                name: "Change a participant",
                                callback: (key, opt, e) => {
                                    const element = opt.$trigger;
                                    $.ajax({
                                        type: "GET",
                                        url: apiURL + '/tournaments/' + tournament_id + '/get-participants',
                                        success: function (result) {
                                            var select = document.createElement('select');
                                            select.setAttribute('class', "form-select");
                                            var index = (element.hasClass("teama")) ? 0 : 1;

                                            select.setAttribute('onChange', "changeParticipant($(this), '" + element.data('bracket') + "', " + index + ")");

                                            result = JSON.parse(result);
                                            if (result.length > 0) {
                                                result.forEach((participant, i) => {
                                                    var option = document.createElement('option');
                                                    option.setAttribute('value', participant.id);
                                                    option.textContent = participant.name;
                                                    if (participant.id == element.data('id')) {
                                                        option.setAttribute('selected', true)
                                                    }

                                                    select.appendChild(option);
                                                });

                                                element.contents().remove();
                                                element.append(select);

                                                editing_mode = true;
                                            } else {
                                                alert("There is no participants to be selected");
                                            }
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
                            
                    items.create = {
                                name: "Add a participant",
                                callback: (key, opt, e) => {
                                    var opts = prompt('Participant Name:', 'Guild');
                                    var index = (opt.$trigger.hasClass("teama")) ? 0 : 1;

                                    if (!_.isNaN(opts)) {
                                        let duplicated = false;
                                        let force_add = false;

                                        $('.bracketbox span[data-round=' + opt.$trigger.data("round") + ']').each((i, ele) => {
                                            if ($(ele).find('.name').text() == opts) {
                                                duplicated = true;
                                                force_add = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");
                                            }
                                        });

                                        if (!duplicated || force_add) {
                                            updateBracket(opt.$trigger, { name: opts, index: index, action_code: addParticipantActionCode, order: (opt.$trigger.data("order") - 1) * 2 + index + 1 });
                                        }
                                    } else
                                        alert('Please input the name of the participant.');
                                }
                    }

                    items.delete = {
                                name: "Delete Bracket",
                                callback: (key, opt, e) => {
                                    var element_id = opt.$trigger.data('bracket');
                                    let triggerElement = opt.$trigger
                                    $.ajax({
                                        type: "delete",
                                        url: apiURL + '/brackets/delete/' + element_id,
                                        success: function (result) {
                                            // var orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + triggerElement.data('next') + "']"), function (ele) { return ele.dataset.order }));
                                            // var index = orders.findIndex((value) => { return value == triggerElement.data('order') });
                                            // document.querySelectorAll('[data-order="' + triggerElement.data('next') + '"]')[index].innerHTML = '&nbsp;';
                                            document.querySelectorAll('[data-order="' + triggerElement.data('order') + '"]').forEach((ele, i) => {
                                                ele.innerHTML = '';
                                                ele.classList.remove("winner");
                                            })
                                            document.querySelectorAll('[data-order="' + triggerElement.data('next') + '"]').forEach((ele, i) => {
                                                // ele.innerHTML = '';
                                            })

                                            // triggerElement.parent().parent().remove();
                                            ws.send('Deleted Brackets!');
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
                    return {
                        items: items
                    }
                }
            });
        }
        // group.append('<div class="r'+(groupCount+1)+'"><div class="final"><div class="bracketbox"><span class="bracket-team teamc">&nbsp;</span></div></div></div>');

        // $('#brackets').append(group);
        $('#brackets').html(group);
        initialize();

        adjustBracketsStyles()

        bracketCount++;
        $('html,body').animate({
            scrollTop: $("#b" + (bracketCount - 1)).offset().top
        });
    }

    function saveBrackets(brackets) {
        $.ajax({
            type: "POST",
            url: apiURL + '/brackets/save-list',
            contentType: "application/json",
            data: JSON.stringify(brackets),
            dataType: "JSON",
            success: function (result) {
                renderBrackets(result.brackets);
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

    function loadBrackets() {

        $.ajax({
            type: "get",
            url: apiURL + '/tournaments/' + tournament_id + '/brackets?uuid=' + UUID,
            success: function (result) {
                result = JSON.parse(result);
                if (result.length > 0) {
                    bracketCount = result.length;
                    brackets = result;

                    drawBrackets();
                }
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
    function initialize(){
        $('#reset-single').on('click', function () {
            $.ajax({
                type: "POST",
                url: apiURL + '/brackets/switch',
                data: { 'type': 'Single', 'tournament_id': tournament_id },
                success: function (result) {
                    alert("Brackets was cleared successfully.");

                    eleminationType = "Single";
                    brackets = [];
                    bracketCount = 0;
                    $('#brackets').html('');
                    ws.send('reset!');
                    loadBrackets()
                },
                error: function (error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function () {
                    $("#overlay").fadeOut(300);
                }, 500);
            });
        });

        $('#reset-double').on('click', function () {
            $.ajax({
                type: "POST",
                url: apiURL + '/brackets/switch',
                data: { type: 'Double', 'tournament_id': tournament_id },
                success: function (result) {
                    alert("Brackets was cleared successfully.");

                    eleminationType = "Double";
                    brackets = [];
                    bracketCount = 0;
                    $('#brackets').html('');
                    ws.send('reset!');
                    loadBrackets()
                },
                error: function (error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function () {
                    $("#overlay").fadeOut(300);
                }, 500);
            });
        });

        $('#clear').on('click', function () {
            $.ajax({
                type: "GET",
                url: apiURL + '/tournaments/' + tournament_id + '/clear',
                success: function (result) {
                    ws.send('reset!');
                    alert("Brackets was cleared successfully.");

                    window.location.href = '/tournaments/' + tournament_id + '/view?mode=edit';
                },
                error: function (error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function () {
                    $("#overlay").fadeOut(300);
                }, 500);
            });
        });

        const stopBtn = document.getElementById('stopMusicButton')
        if (stopBtn) {
            stopBtn.addEventListener('click', function () {
                stopMusicPlaying()
            });
        }
    }
    
    function updateBracket(element, data) {
        $("#overlay").fadeIn(300);

        $.ajax({
            type: "put",
            url: apiURL + '/brackets/update/' + element.data('bracket'),
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "JSON",
            success: function (result) {
                ws.send('updated!');
                loadBrackets();
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

    function markWinner(key, opt, e) {
        let orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + opt.$trigger.data('next') + "']"), function (ele) { return ele.dataset.order }));
        let index = orders.findIndex((value) => { return value == opt.$trigger.data('order') });
        const next_id = opt.$trigger.data('next');
        let next_bracketObj = document.querySelectorAll('[data-order="' + next_id + '"]')[index];
        next_bracket = next_bracketObj.dataset.bracket;
        const nameSpan = opt.$trigger.find('.name').clone()
        const pimageDiv = opt.$trigger.find('.p-image').clone();

        let is_final = false
        if (next_bracketObj.parentElement.classList.contains('final')) {
            is_final = true
        }

        $.ajax({
            type: "PUT",
            url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
            contentType: "application/json",
            data: JSON.stringify({ winner: opt.$trigger.data('id'), order: opt.$trigger.data('p_order'), action_code: markWinnerActionCode, is_final: is_final, index: index }),
            success: function (result) {
                ws.send('marked!');
                loadBrackets()
                
                if (next_bracketObj.parentElement.classList.contains('final')) {
                    next_bracketObj.classList.add('winner');

                    var player = document.getElementById('myAudio');
                    if (player) {
                        player.addEventListener("timeupdate", function () {
                            if ((player.currentTime - player._startTime) >= player.value) {
                                player.pause();
                                document.getElementById('stopMusicButton').classList.add('d-none');
                            };
                        });

                        player.value = player.dataset.duration;
                        player._startTime = player.dataset.starttime;
                        player.currentTime = player.dataset.starttime;
                        player.play();
                    }

                    if (document.getElementById('stopMusicButton')) {
                        document.getElementById('stopMusicButton').classList.remove('d-none');
                        document.getElementById('stopMusicButton').textContent = "Pause Music"
                    }
                    
                }
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

    function unmarkWinner(key, opt, e) {
        const next_id = opt.$trigger.data('next');
        let orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + next_id + "']"), function (ele) { return ele.dataset.order }));
        let index = orders.findIndex((value) => { return value == opt.$trigger.data('order') });
        let next_bracketObj = document.querySelectorAll('[data-order="' + next_id + '"]')[index];
        next_bracket = next_bracketObj.dataset.bracket;

        let is_final = false
        if (next_bracketObj.parentElement.classList.contains('final')) {
            is_final = true
        }

        const ele = opt.$trigger;
        $.ajax({
            type: "PUT",
            url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
            contentType: "application/json",
            data: JSON.stringify({ action_code: unmarkWinnerActionCode, participant: opt.$trigger.data('id'), index: index, is_final: is_final}),
            success: function (result) {
                ws.send('unmarked!');
                // ele.find('.score').remove()

                loadBrackets()

                if (document.getElementById('stopMusicButton')) {
                    document.getElementById('stopMusicButton').classList.add('d-none');
                    document.getElementById('stopMusicButton').textContent = "Pause Music"
                }
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

function changeParticipant(ele, bracket_id, index) {
    let ability = true;
    $('.bracketbox span[data-round=' + ele.parent().data("round") + ']').each((i, e) => {
        if (e.dataset.id == ele.val()) {
            let confirm_result = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");

            if (confirm_result == false) {
                ability = false;
                return false;
            }
        }
    });

    if (ability) {
        let participant_order = ele.parent().data('p_order')
        if (!participant_order) {
            participant_order = (parseInt(ele.parent().data('order')) - 1) * 2 + index + 1
        }

        editing_mode = false;

        updateBracket(ele.parent(), { name: ele.find("option:selected").text(), index: index, participant: ele.find("option:selected").val(), action_code: changeParticipantActionCode, order: participant_order });
    }
}

function adjustBracketsStyles() {
  const rows = document.querySelectorAll(".brackets div.groups div.bracketbox-list");
  const baseHeight = 30;
  const baseMargin = 40;
  
  rows.forEach((row, index) => {
    const multiplier = Math.pow(2, index + 1);
    const height = baseHeight * multiplier;
    const margin = baseHeight * Math.pow(2, index) + baseMargin / 2;
      
    row.querySelectorAll('.bracketbox').forEach((bracket, index) => {
        
        if (bracket.classList.contains('final')) {
            bracket.style.height = `0`;
            bracket.style.margin = `${margin}px 0 0`;    
        } else {
            bracket.style.height = `${height}px`;
            if (row.querySelectorAll('.bracketbox').length > index + 1) {
                bracket.style.margin = `${margin}px 0 ${height}px`;
            } else {
                bracket.style.margin = `${margin}px 0`;
            }
            
        }
    })
  });
}

function chooseImage(e, element_id){
    $("#image_" + element_id).trigger('click');
}
function checkBig(el, element_id) {
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!allowedTypes.includes(el.files[0].type)) {
        alert('Error uploading image! Please upload image as *.jpeg, *.jpg, *.png, *.gif format.');
        this.value = '';
        return
    }

    if(el.files[0].size > 1048576){
        alert('Error uploading image! Max image size is 1MB. Please upload small image.');
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
                ws.send('marked!');
                loadBrackets();
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
            ws.send('marked!')
            loadBrackets();
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

let submitVote = (event) => {
    const participant_element = $(event.currentTarget).parents('.bracket-team')
    $.ajax({
        type: "POST",
        url: apiURL + '/tournaments/vote',
        data: {
            'tournament_id': tournament_id,
            'participant_id': participant_element.data('id'),
            'bracket_id': participant_element.data('order'),
            'round_no': participant_element.data('round'),
            'uuid': UUID
        },
        dataType: "JSON",
        success: function (result) {
            $('span[data-id="' + result.data.participant_id + '"] .votes').each((i, element) => {
                $(element).text(result.data.votes)
            })

            // Save vote history to local storage
            const storage_key = 'vote_t' + tournament_id + '_n' + result.data.round_no + '_b' + result.data.bracket_id
            window.localStorage.setItem(storage_key, result.data.participant_id)

            loadBrackets()

            // triggerElement.parent().parent().remove();
            ws.send('Vote the participant!');
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

let enableChangeRoundName = (event) => {
    const container = document.createElement('div')
    container.classList.add("input-group")

    const nameBox = document.createElement('input');
    const name = $(event.currentTarget.parentElement).find('.round-name').eq(0).html();
    nameBox.classList.add('name', 'form-control');
    nameBox.value = name;
    nameBox.setAttribute('data-name-label', name)

    const confirmBtn = document.createElement('button')
    confirmBtn.classList.add('btn', 'btn-outline-secondary')
    confirmBtn.innerHTML = '<span class="fa fa-check">'
    confirmBtn.addEventListener('click', (event) => {
        saveRoundName(event)
    })

    const cancelBtn = document.createElement('button')
    cancelBtn.classList.add('btn', 'btn-outline-secondary')
    cancelBtn.innerHTML = `<span class="fa fa-close">`
    cancelBtn.addEventListener('click', (event) => {
        cancelChangeRoundName(event, `${name}`)
    })

    container.append(nameBox)
    container.append(confirmBtn)
    container.append(cancelBtn)

    $(event.currentTarget.parentElement).html(container)
}

let cancelChangeRoundName = (event, name) => {
    let html = `<span class="round-name">${name}</span> <span class="fa fa-pencil" onclick="enableChangeRoundName(event)"></span>`
    event.currentTarget.parentElement.parentElement.innerHTML = html
}

let saveRoundName = (event) => {
    const name = event.currentTarget.value
    $.ajax({
        type: "POST",
        url: apiURL + '/brackets/save-round',
        data: {
            'tournament_id': tournament_id,
            'round_no': event.currentTarget.parentElement.parentElement.dataset.roundNo,
            'round_name': event.currentTarget.parentElement.firstChild.value
        },
        dataType: "JSON",
        success: function (result) {
            loadBrackets()

            // triggerElement.parent().parent().remove();
            ws.send('Vote the participant!');
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