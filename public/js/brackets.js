let bracketCount = 0,
    brackets = [];

let eleminationType = "Single";
let editing_mode = false;

$(document).on('ready', function () {
    loadBrackets();

    /*
     * Build our bracket "model"
     */
    function drawBrackets() {
        if (brackets.length > 0)
            renderBrackets(brackets);
    }

    function makeDoubleElimination(struct) {
        var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;

        var grouped = _.groupBy(struct, function (s) { return s.roundNo; });

        var doubleEliminationBrackets = [], bracketNo = 1, nextInc = grouped[1].length * 2, base = nextInc * 2, i = 1;
        for (round = 1; round <= groupCount; round++) {
            var last = _.map(_.filter(doubleEliminationBrackets, function (b) { return b.nextGame == i; }), function (b) { return { game: b.bracketNo, teams: b.teamnames }; });

            _.each(grouped[round], function (gg) {

                doubleEliminationBrackets.push({
                    lastGames: round == 1 ? null : [last[0].game, last[1].game],
                    nextGame: nextInc + i > base ? null : nextInc + i,
                    teamnames: gg.teamnames,
                    bracketNo: bracketNo++,
                    roundNo: round,
                    bye: gg.bye
                });

                if (i % 2 != 0) nextInc--;
                i++;
            });

            if (i < base) {
                _.each(grouped[round], function (gg) {
                    doubleEliminationBrackets.push({
                        lastGames: round == 1 ? null : [last[0].game, last[1].game],
                        nextGame: nextInc + i > base ? null : nextInc + i,
                        teamnames: gg.teamnames,
                        bracketNo: bracketNo++,
                        roundNo: round,
                        bye: gg.bye
                    });

                    if (i % 2 != 0) nextInc--;
                    i++;
                });
            }
        }

        doubleEliminationBrackets.push({
            lastGames: round == 1 ? null : [last[0].game, last[1].game],
            nextGame: nextInc + i >= base ? null : nextInc + i,
            teamnames: [undefined, undefined],
            bracketNo: bracketNo++,
            roundNo: round,
            bye: null,
            final_match: true
        });

        return doubleEliminationBrackets;
    }

    /*
     * Inject our brackets
     */
    function renderBrackets(struct) {
        var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;

        var group = $('<div class="groups group' + (groupCount + 1) + '" id="b' + bracketCount + '" style="min-width:' + 160 * (groupCount + 1) + "px" + '"></div>'),
            grouped = _.groupBy(struct, function (s) { return s.roundNo; });

        // document.getElementById('brackets').style.width = 170 * (groupCount + 1) + 'px';

        for (g = 1; g <= groupCount; g++) {
            var round = $('<div class="r' + g + '"></div>');
            _.each(grouped[g], function (gg) {

                // if(gg.bye)
                // round.append('<div></div>');
                // else {
                var obj = document.createElement('span');
                obj.dataset.order = gg.bracketNo;
                obj.dataset.bracket = gg.id;
                obj.dataset.next = gg.nextGame;
                obj.dataset.round = gg.roundNo;
                obj.textContent = ' ';

                var teams = JSON.parse(gg.teamnames);
                var teama = obj.cloneNode();
                teama.className = 'bracket-team teama';
                if (teams[0] != undefined) {
                    teama.dataset.id = teams[0].id;
                    teama.textContent = teams[0].name;

                    if (teams[0].id == gg.winner)
                        teama.classList.add('winner');

                }

                var teamb = obj.cloneNode();
                teamb.className = 'bracket-team teamb';
                if (teams[1] != undefined) {
                    teamb.dataset.id = teams[1].id;
                    teamb.textContent = teams[1].name;

                    if (teams[1].id == gg.winner)
                        teamb.classList.add('winner');
                }

                var container = document.createElement('div');

                if (gg.final_match) {
                    container.className = "final";
                    teama.className = (teams[0]) ? "teama winner" : 'teama';
                }

                var bracket = document.createElement('div')
                bracket.className = "bracketbox";

                bracket.append(teama);

                if (!gg.final_match || gg.final_match === undefined)
                    bracket.append(teamb);

                container.append(bracket);

                round.append(container);
                // }

            });

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
                                        url: apiURL + '/participants/',
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
                                            if (ele.textContent == opts) {
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

        $('#brackets').append(group);

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
        $("#overlay").fadeIn(300);

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
                alert("Brackets was cleared successfully.");

                window.location.href = '/tournaments/' + tournament_id + '/view';
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
            let box = element;
            box.data('id', result.data.participant_id);
            box.contents().remove();
            box.append(data.name);

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
    const text = opt.$trigger.text();

    $.ajax({
        type: "PUT",
        url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
        contentType: "application/json",
        data: JSON.stringify({ winner: opt.$trigger.data('id'), action_code: markWinnerActionCode }),
        success: function (result) {
            document.querySelectorAll('[data-order="' + next_id + '"]')[index].innerHTML = text;
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
        data: JSON.stringify({ index: index, participant: opt.$trigger.data('id'), name: opt.$trigger.text() }),
        success: function (result) {
            ele.parent().contents().removeClass('winner')
            ele.addClass('winner');
            next_bracketObj.dataset.id = ele.data('id');
            next_bracketObj.innerHTML = ele.text();

            if (next_bracketObj.parentElement.parentElement.classList.contains('final')) {
                next_bracketObj.classList.add('winner');

                var player = document.getElementById('myAudio');
                player.addEventListener("timeupdate", function () {
                    if ((player.currentTime - player._startTime) >= player.value) {
                        player.pause();
                    };
                });

                player.value = player.dataset.duration;
                player._startTime = player.dataset.starttime;
                player.currentTime = player.dataset.starttime;
                player.play();
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

    $.ajax({
        type: "PUT",
        url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
        contentType: "application/json",
        data: JSON.stringify({ winner: '' }),
        success: function (result) {
            document.querySelectorAll('[data-order="' + next_id + '"]')[index].innerHTML = '';
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
        data: JSON.stringify({ index: index, participant: opt.$trigger.data('id'), name: '', action_code: unmarkWinnerActionCode }),
        success: function (result) {
            ele.parent().contents().removeClass('winner')
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