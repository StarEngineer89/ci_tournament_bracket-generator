let apiURL = "http://localhost:8080/api";

var knownBrackets = [2,4,8,16,32], // brackets with "perfect" proportions (full fields, no byes)
    
bracketCount = 0,

brackets = [];

let eleminationType = "Single";
let editing_mode = false;

$(document).on('ready', function() {
    loadBrackets();

    /*
     * Build our bracket "model"
     */
    function drawBrackets () {
        if (brackets.length > 0)
            renderBrackets(brackets);
    }

    function makeDoubleElimination(struct) {
        var groupCount	= _.uniq(_.map(struct, function(s) { return s.roundNo; })).length;
        
        var grouped = _.groupBy(struct, function(s) { return s.roundNo; });
        
        var doubleEliminationBrackets = [], bracketNo = 1, nextInc = grouped[1].length * 2, base = nextInc * 2, i = 1;
        for(round=1; round<=groupCount; round++) {
            var last = _.map(_.filter(doubleEliminationBrackets, function(b) { return b.nextGame == i; }), function(b) { return {game:b.bracketNo,teams:b.teamnames}; });

            _.each(grouped[round], function(gg) {
                
                doubleEliminationBrackets.push({
                    lastGames:	round==1 ? null : [last[0].game,last[1].game],
                    nextGame:	nextInc+i>base?null:nextInc+i,
                    teamnames:	gg.teamnames,
                    bracketNo:	bracketNo++,
                    roundNo:	round,
                    bye:		gg.bye
                });

                if(i%2!=0)	nextInc--;
                i++;
            });

            if (i < base) {
                _.each(grouped[round], function(gg) {
                    doubleEliminationBrackets.push({
                        lastGames:	round==1 ? null : [last[0].game,last[1].game],
                        nextGame:	nextInc+i>base?null:nextInc+i,
                        teamnames:	gg.teamnames,
                        bracketNo:	bracketNo++,
                        roundNo:	round,
                        bye:		gg.bye
                    });
    
                    if(i%2!=0)	nextInc--;
                    i++;
                });
            }
        }

        doubleEliminationBrackets.push({
            lastGames:	round==1 ? null : [last[0].game,last[1].game],
            nextGame:	nextInc+i>=base?null:nextInc+i,
            teamnames:	[undefined,undefined],
            bracketNo:	bracketNo++,
            roundNo:	round,
            bye:		null,
            final_match: true
        });

        return doubleEliminationBrackets;
    }

    /*
     * Inject our brackets
     */
    function renderBrackets(struct) {
        var groupCount	= _.uniq(_.map(struct, function(s) { return s.roundNo; })).length;
        
        var group	= $('<div class="group'+(groupCount+1)+'" id="b'+bracketCount+'"></div>'),
            grouped = _.groupBy(struct, function(s) { return s.roundNo; });
        
        for(g=1;g<=groupCount;g++) {
            var round = $('<div class="r'+g+'"></div>');
            _.each(grouped[g], function(gg) {
                
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
                        teama.className = 'teama';
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
        
        $.contextMenu({
            selector: '.bracket-team',
            items: {
                mark: {
                    name: "Mark as Winner",
                    callback: (key, opt, e) => {
                        if (editing_mode) {
                            alert('You should change the participant first');
                            return;
                        }

                        let orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + opt.$trigger.data('next') + "']"), function(ele) {return ele.dataset.order}));
                        let index = orders.findIndex((value) => {return value == opt.$trigger.data('order')});
                        let next_bracketObj = document.querySelectorAll('[data-order="' + opt.$trigger.data('next') + '"]')[index];
                        next_bracket = next_bracketObj.dataset.bracket;

                        $.ajax({
                            type: "PUT",
                            url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
                            contentType: "application/json",
                            data: JSON.stringify({winner: opt.$trigger.data('id')}),
                            success: function(result) {
                                opt.$trigger.addClass('winner');                                
                                document.querySelectorAll('[data-order="' + opt.$trigger.data('next') + '"]')[index].innerHTML = opt.$trigger.text();
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        }).done(() => {
                            setTimeout(function(){
                                $("#overlay").fadeOut(300);
                            },500);
                        });

                        $.ajax({
                            type: "PUT",
                            url: apiURL + '/brackets/update/' + next_bracket,
                            contentType: "application/json",
                            data: JSON.stringify({index: index, participant: opt.$trigger.data('id'), name: opt.$trigger.text()}),
                            success: function(result) {
                                opt.$trigger.parent().contents().removeClass('winner')
                                opt.$trigger.addClass('winner');
                                next_bracketObj.dataset.id = opt.$trigger.data('id');
                                next_bracketObj.innerHTML = opt.$trigger.text();
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        }).done(() => {
                            setTimeout(function(){
                                $("#overlay").fadeOut(300);
                            },500);
                        });
                    }
                },
                change: {
                    name: "Change a participant",
                    callback: (key, opt, e) => {
                        $.ajax({
                            type: "GET",
                            url: apiURL + '/participants/',
                            success: function(result) {
                                var select = document.createElement('select');
                                select.setAttribute('class', "form-select");
                                var index = (opt.$trigger.hasClass("teama")) ? 0 : 1;
                                
                                select.setAttribute('onChange', "changeParticipant($(this), '" + opt.$trigger.data('bracket') + "', " + index + ")");

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

                                    opt.$trigger.contents().remove();
                                    opt.$trigger.append(select);

                                    editing_mode = true;
                                } else {
                                    alert("There is no participants to be selected");
                                }
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        }).done(() => {
                            setTimeout(function(){
                                $("#overlay").fadeOut(300);
                            },500);
                        });
                    }
                },
                create: {
                    name: "Add a participant",
                    callback: (key, opt, e) => {
                        var opts = prompt('Participant Name:', 'Guild');
                        var index = (opt.$trigger.hasClass("teama")) ? 0 : 1;
        
                        if(!_.isNaN(opts)) {
                            let duplicated = false;
                            let force_add = false;
                            
                            $('.bracketbox span[data-round=' + opt.$trigger.data("round") + ']').each((i, ele) => {
                                if (ele.textContent == opts) {
                                    duplicated = true;
                                    force_add = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");
                                }
                            });

                            if (!duplicated || force_add) {
                                updateBracket(opt.$trigger, { name: opts, index: index });
                            }
                        } else
                            alert('Please input the name of the participant.');
                    }
                },
                delete: {
                    name: "Delete a bracket",
                    callback: (key, opt, e) => {
                        var element_id = opt.$trigger.data('bracket');
                        $.ajax({
                            type: "delete",
                            url: apiURL + '/brackets/delete/' + element_id,
                            success: function(result) {
                                var orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + opt.$trigger.data('next') + "']"), function(ele) {return ele.dataset.order}));
                                var index = orders.findIndex((value) => {return value == opt.$trigger.data('order')});
                                document.querySelectorAll('[data-order="' + opt.$trigger.data('next') + '"]')[index].innerHTML = '&nbsp;';
                                
                                opt.$trigger.parent().parent().remove();
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        }).done(() => {
                            setTimeout(function(){
                                $("#overlay").fadeOut(300);
                            },500);
                        });
                    }
                }
            }
        });

        // group.append('<div class="r'+(groupCount+1)+'"><div class="final"><div class="bracketbox"><span class="bracket-team teamc">&nbsp;</span></div></div></div>');

        $('#brackets').append(group);
        
        bracketCount++;
        $('html,body').animate({
            scrollTop: $("#b"+(bracketCount-1)).offset().top
        });
    }

    function saveBrackets(brackets) {
        $.ajax({
            type: "POST",
            url: apiURL + '/brackets/save-list',
            contentType: "application/json",
            data: JSON.stringify(brackets),
            dataType: "JSON",
            success: function(result) {
                renderBrackets(result.brackets);
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        });
    }
    
    function loadBrackets() {
        $("#overlay").fadeIn(300);
    
        $.ajax({
            type: "get",
            url: apiURL + '/brackets/',
            success: function(result) {
                result = JSON.parse(result);
                if (result.length > 0) {
                    bracketCount = result.length;
                    brackets = result;

                    drawBrackets();
                }
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        });
    }

    $('#reset-single').on('click', function() {
        $.ajax({
            type: "POST",
            url: apiURL + '/brackets/switch',
            data: {type: 'Single'},
            success: function(result) {
                alert("Brackets was cleared successfully.");

                eleminationType = "Single";
                brackets = [];
                bracketCount = 0;
                $('#brackets').html('');
                loadBrackets()
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        });
    });

    $('#reset-double').on('click', function() {
        $.ajax({
            type: "POST",
            url: apiURL + '/brackets/switch',
            data: {type: 'Double'},
            success: function(result) {
                alert("Brackets was cleared successfully.");

                eleminationType = "Double";
                brackets = [];
                bracketCount = 0;
                $('#brackets').html('');
                loadBrackets()
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        });
    });

    $('#clear').on('click', function() {
        $.ajax({
            type: "GET",
            url: apiURL + '/brackets/clear',
            success: function(result) {
                alert("Brackets was cleared successfully.");

                window.location.href = '/';
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        });
    });
    
});

function changeParticipant(ele, bracket_id, index) {
    $('.bracketbox span[data-round=' + ele.parent().data("round") + ']').each((i, e) => {
        if (e.dataset.id == ele.val()) {
            let confirm_result = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");

            if (confirm_result == true) {
                updateBracket(ele.parent(), { name: ele.find("option:selected").text(), index: index, participant: ele.find("option:selected").val() });
            }
        }
    });
}

function updateBracket(element, data) {
    $("#overlay").fadeIn(300);

    $.ajax({
        type: "put",
        url: apiURL + '/brackets/update/'+element.data('bracket'),
        data: JSON.stringify(data),
        contentType: "application/json",
        dataType: "JSON",
        success: function(result) {
            let box = element;
            box.data('id', result.data.participant_id);
            box.contents().remove();
            box.append(data.name);

            editing_mode = false;
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function(){
            $("#overlay").fadeOut(300);
        },500);
    });
}