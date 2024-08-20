let bracketCount = 0,
    brackets = [];

let eleminationType = "Single";
let editing_mode = false;
var ws;

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


    /*
     * Build our bracket "model"
     */
    function drawBrackets() {
        if (brackets.length > 0)
            renderBrackets(brackets);
    }

    /*
     * Inject our brackets
     */
    function renderBrackets(struct) {
        var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;

        var group = $('<div class="groups group' + (groupCount + 1) + '" id="b' + bracketCount + '" style="min-width:' + 190 * groupCount + "px" + '"></div>'),
            grouped = _.groupBy(struct, function (s) { return s.roundNo; });

        // document.getElementById('brackets').style.width = 170 * (groupCount + 1) + 'px';

        for (g = 1; g <= groupCount; g++) {
            var round = $('<div class="r' + g + '"></div>');
            
            var roundName = $('<div class="text-center p-2 m-1 border" style="height: auto"></div>')
            if (grouped[g][0].final_match && grouped[g][0].final_match !== "0") {
                roundName.html("Round " + grouped[g][0].roundNo + ': Grand Final') 
            } else {
                roundName.html("Round " + grouped[g][0].roundNo) 
            }
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

                var pidBox = document.createElement('span')
                pidBox.classList.add('p-id')

                var teams = JSON.parse(gg.teamnames);
                var teama = obj.cloneNode(true);
                teama.className = 'bracket-team teama';
                if (teams[0] != undefined) {
                    var pid = pidBox.cloneNode(true)
                    pid.textContent = parseInt(teams[0].order) + 1
                    teama.appendChild(pid)

                    teama.dataset.id = teams[0].id;
                    teama.dataset.p_order = teams[0].order;
                    var nameSpan = document.createElement('span')
                    nameSpan.classList.add('name')
                    nameSpan.textContent = teams[0].name;
                    teama.appendChild(nameSpan)

                    var score = scoreBox.cloneNode(true)
                    var scorePoint = scoreBracket * (g - 1)
                    for (round_i = 0; round_i < g - 1; round_i++) {
                        scorePoint += incrementScore * round_i
                    }

                    if (teams[0].id == gg.winner) {
                        teama.classList.add('winner');
                        scorePoint += scoreBracket
                        scorePoint += incrementScore * (g - 1)
                    }

                    if (isScoreEnabled) {
                        score.textContent = scorePoint
                        teama.appendChild(score)
                    }
                }

                var teamb = obj.cloneNode(true);
                teamb.className = 'bracket-team teamb';
                if (teams[1] != undefined) {
                    var pid = pidBox.cloneNode(true)
                    pid.textContent = parseInt(teams[1].order) + 1
                    teamb.appendChild(pid)

                    teamb.dataset.id = teams[1].id;
                    teamb.dataset.p_order = teams[1].order;
                    var nameSpan = document.createElement('span')
                    nameSpan.classList.add('name')
                    nameSpan.textContent = teams[1].name
                    teamb.appendChild(nameSpan)

                    var score = scoreBox.cloneNode(true)
                    var scorePoint = scoreBracket * (g - 1)
                    for (round_i = 0; round_i < g - 1; round_i++) {
                        scorePoint += incrementScore * round_i
                    }

                    if (teams[1].id == gg.winner) {
                        teamb.classList.add('winner');
                        scorePoint += scoreBracket
                        scorePoint += incrementScore * (g - 1)
                    }

                    if (isScoreEnabled) {
                        score.textContent = scorePoint
                        teamb.appendChild(score)
                    }
                }

                var bracket = document.createElement('div')

                if (gg.final_match && gg.final_match !== "0") {
                    bracket.className = "bracketbox final";
                    teama.className = (teams[0]) ? "teama winner" : 'teama';
                } else {
                    var bracketNo = document.createElement('span')
                    bracketNo.classList.add('bracketNo')
                    bracketNo.innerHTML = gg.bracketNo
                    bracket.append(bracketNo)
                    bracket.className = "bracketbox";
                }

                bracket.append(teama);

                if (!gg.final_match || gg.final_match === undefined || gg.final_match === '0')
                    bracket.append(teamb);

                bracketBoxList.append(bracket);
                // }

            });

            round.append(bracketBoxList)

            group.append(round);
        }

        if (hasEditPermission) {
            $.contextMenu({
                selector: '.bracket-team',
                build: function ($triggerElement, e) {
                    let isWinner = ($triggerElement.hasClass('winner')) ? true : false;
                    return {
                        items: {
                            mark: {
                                name: (!isWinner) ? "Mark as Winner" : "Unmark as winner",
                                callback: (key, opt, e) => {
                                    if (!isWinner)
                                        markWinner(key, opt, e)
                                    else
                                        unmarkWinner(key, opt, e)
                                },
                            },
                            change: {
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

                                            var option = document.createElement('option');
                                            select.appendChild(option);

                                            result = JSON.parse(result);
                                            if (result.length > 0) {
                                                result.forEach((participant, i) => {
                                                    var option = document.createElement('option');
                                                    option.setAttribute('value', participant.id);
                                                    option.textContent = participant.name;

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
                            },
                            create: {
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
                                            updateBracket(opt.$trigger, { name: opts, index: index, action_code: addParticipantActionCode });
                                        }
                                    } else
                                        alert('Please input the name of the participant.');
                                }
                            },
                            delete: {
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
            url: apiURL + '/tournaments/' + tournament_id + '/brackets',
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
});

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
        updateBracket(ele.parent(), { name: ele.find("option:selected").text(), index: index, participant: ele.find("option:selected").val(), action_code: changeParticipantActionCode });
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
            let box = element;
            box.data('id', result.data.participant_id);
            box.contents().remove();

            var nameSpan = document.createElement('span')
            nameSpan.classList.add('name')
            nameSpan.textContent = data.name
            
            box.append(nameSpan);

            if (isScoreEnabled) {
                var scoreBox = document.createElement('span')
                scoreBox.classList.add('score')
                var scorePoint = 0
                scoreBox.textContent = scorePoint
                box.append(scoreBox)
            }

            editing_mode = false;
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
    if (editing_mode) {
        alert('You should change the participant first');
        return;
    }

    let orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + opt.$trigger.data('next') + "']"), function (ele) { return ele.dataset.order }));
    let index = orders.findIndex((value) => { return value == opt.$trigger.data('order') });
    const next_id = opt.$trigger.data('next');
    let next_bracketObj = document.querySelectorAll('[data-order="' + next_id + '"]')[index];
    next_bracket = next_bracketObj.dataset.bracket;
    const nameSpan = opt.$trigger.find('.name').clone()

    let is_final = false
    if (next_bracketObj.parentElement.classList.contains('final')) {
        is_final = true
    }

    $.ajax({
        type: "PUT",
        url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
        contentType: "application/json",
        data: JSON.stringify({ winner: opt.$trigger.data('id'), order: opt.$trigger.data('p_order'), action_code: markWinnerActionCode, is_final: is_final }),
        success: function (result) {
            ws.send('marked!');
            console.log(result)
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });

    const ele = opt.$trigger;
    $.ajax({
        type: "PUT",
        url: apiURL + '/brackets/update/' + next_bracket,
        contentType: "application/json",
        data: JSON.stringify({ index: index, participant: opt.$trigger.data('id'), name: opt.$trigger.find('.name').text(), order: opt.$trigger.data('p_order') }),
        success: function (result) {
            ws.send('marked!');
            $(next_bracketObj).contents().remove()
            ele.parent().contents().removeClass('winner')
            ele.addClass('winner');
            
            if (isScoreEnabled) {
                var scoreBox = document.createElement('span')
                scoreBox.classList.add('score')
                var scorePoint = 0
                for (round_i = 0; round_i < parseInt(ele.data('round')); round_i++) {
                    scorePoint += scoreBracket
                    scorePoint += incrementScore * round_i
                }
                scoreBox.textContent = scorePoint
                ele.append(scoreBox)
            }

            next_bracketObj.dataset.id = ele.data('id');
            $(next_bracketObj).append(nameSpan);
            var pidBox = document.createElement('span')
            pidBox.classList.add('p-id')
            pidBox.textContent = parseInt(ele.data('p_order')) + 1
            $(next_bracketObj).prepend(pidBox)

            if (isScoreEnabled) {
                scoreBox = document.createElement('span')
                scoreBox.classList.add('score')
                scoreBox.textContent = scorePoint
                $(next_bracketObj).append(scoreBox)
            }

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
        data: JSON.stringify({ winner: '' }),
        success: function (result) {
            ws.send('unmarked!');
            ele.find('.score').remove()

            if (isScoreEnabled) {
                var scoreBox = document.createElement('span')
                scoreBox.classList.add('score')
                var scorePoint = scoreBracket * (parseInt(ele.data('round')) - 1)
                for (round_i = 0; round_i < parseInt(ele.data('round')) - 1; round_i++) {
                    scorePoint += incrementScore * round_i
                }
                scoreBox.textContent = scorePoint
                ele.append(scoreBox)
            }

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

    $.ajax({
        type: "PUT",
        url: apiURL + '/brackets/update/' + next_bracket,
        contentType: "application/json",
        data: JSON.stringify({ index: index, participant: opt.$trigger.data('id'), name: '', action_code: unmarkWinnerActionCode, is_final: is_final }),
        success: function (result) {
            ws.send('unmarked!');
            ele.parent().contents().removeClass('winner')
            next_bracketObj.classList.remove('winner')
            next_bracketObj.dataset.id = '';
            next_bracketObj.innerHTML = '';
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
