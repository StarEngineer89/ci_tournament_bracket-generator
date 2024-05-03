let apiURL = "http://86.104.72.173:5000/api";
// let apiURL = "http://localhost:5000/api";
var knownBrackets = [2,4,8,16,32], // brackets with "perfect" proportions (full fields, no byes)
    
exampleTeams  = [], // because a bracket needs some teams!

bracketCount = 0,

brackets = [];

let eleminationType = "Single";
let editing_mode = false;

$(document).on('ready', function() {
    
    /*
     * Build our bracket "model"
     */
    function drawBrackets () {
        if (brackets.length > 0)
            renderBrackets(brackets);
        else
            generateBracket(exampleTeams.length);
    }

    function generateBracket(base) {
    
        var closest 		= _.find(knownBrackets, function(k) { return k>=base; }),
            byes 			= closest-base;
            
        if(byes>0)	base = closest;
    
        var brackets 	= [],
            round 		= 1,
            baseT 		= base/2,
            baseC 		= base/2,
            teamMark	= 0,
            nextInc		= base/2;
        
        for(i=1;i<=(base-1);i++) {
            var	baseR = i/baseT,
                isBye = false;
                
            if(byes>0 && (i%2!=0 || byes>=(baseT-i))) {
                isBye = true;
                byes--;
            }
            
            var last = _.map(_.filter(brackets, function(b) { return b.nextGame == i; }), function(b) { return {game:b.bracketNo,teams:b.teamnames}; });
            
            brackets.push({
                lastGames:	round==1 ? null : [last[0].game,last[1].game],
                nextGame:	nextInc+i>base?null:nextInc+i,
                teamnames:	round==1 ? [exampleTeams[teamMark],exampleTeams[teamMark+1]] : [undefined,undefined],
                bracketNo:	i,
                roundNo:	round,
                bye:		isBye
            });
            teamMark+=2;
            if(i%2!=0)	nextInc--;
            while(baseR>=1) {
                round++;
                baseC/= 2;
                baseT = baseT + baseC;
                baseR = i/baseT;
            }
        }

        brackets.push({
            lastGames:	round==1 ? null : [last[0].game,last[1].game],
            nextGame:	nextInc+i>=base?null:nextInc+i,
            teamnames:	round==1 ? [exampleTeams[teamMark],exampleTeams[teamMark+1]] : [undefined,undefined],
            bracketNo:	i,
            roundNo:	round,
            bye:		isBye,
            final_result: true
        });        
            
        var struct = brackets;

        if (eleminationType == "Double")
            struct = makeDoubleElimination(brackets);
        
        saveBrackets(struct);
    }

    function getBracket_origin(base) {
    
        var closest 		= _.find(knownBrackets, function(k) { return k>=base; }),
            byes 			= closest-base;
            
        if(byes>0)	base = closest;
    
        var brackets 	= [],
            round 		= 1,
            baseT 		= base/2,
            baseC 		= base/2,
            teamMark	= 0,
            nextInc		= base/2;
            
        for(i=1;i<=(base-1);i++) {
            var	baseR = i/baseT,
                isBye = false;
                
            if(byes>0 && (i%2!=0 || byes>=(baseT-i))) {
                isBye = true;
                byes--;
            }
            
            var last = _.map(_.filter(brackets, function(b) { return b.nextGame == i; }), function(b) { return {game:b.bracketNo,teams:b.teamnames}; });
            
            brackets.push({
                lastGames:	round==1 ? null : [last[0].game,last[1].game],
                nextGame:	nextInc+i>base-1?null:nextInc+i,
                teamnames:	round==1 ? [exampleTeams[teamMark],exampleTeams[teamMark+1]] : [last[0].teams[_.random(1)],last[1].teams[_.random(1)]],
                bracketNo:	i,
                roundNo:	round,
                bye:		isBye
            });
            teamMark+=2;
            if(i%2!=0)	nextInc--;
            while(baseR>=1) {
                round++;
                baseC/= 2;
                baseT = baseT + baseC;
                baseR = i/baseT;
            }
        }
        
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
            final_result: true
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

                    var teama = obj.cloneNode();
                    teama.className = 'bracket-team teama';                    
                    if (gg.teamnames[0] != undefined) {
                        teama.dataset.id = gg.teamnames[0].id;
                        teama.textContent = gg.teamnames[0].name;
                        
                        if (gg.teamnames[0].id == gg.winner)
                            teama.classList.add('winner');

                    }

                    var teamb = obj.cloneNode();
                    teamb.className = 'bracket-team teamb';
                    if (gg.teamnames[1] != undefined) {
                        teamb.dataset.id = gg.teamnames[1].id;
                        teamb.textContent = gg.teamnames[1].name;
                        
                        if (gg.teamnames[1].id == gg.winner)
                            teamb.classList.add('winner');
                    }

                    var container = document.createElement('div');
                    
                    if (gg.final_result) {
                        container.className = "final";
                        teama.className = 'teama';
                    }
                    
                    var bracket = document.createElement('div')
                    bracket.className = "bracketbox";
                    
                    bracket.append(teama);

                    if (!gg.final_result || gg.final_result === undefined) 
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
                            url: apiURL + '/bracket/update/' + opt.$trigger.data('bracket'),
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
                            url: apiURL + '/bracket/update/' + next_bracket,
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
                            url: apiURL + '/participant/',
                            success: function(result) {
                                var select = document.createElement('select');
                                select.setAttribute('class', "form-select");
                                var index = (opt.$trigger.hasClass("teama")) ? 0 : 1;
                                
                                select.setAttribute('onChange', "changeParticipant($(this), '" + opt.$trigger.data('bracket') + "', " + index + ")");

                                var option = document.createElement('option');
                                select.appendChild(option);

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
                            
                            $('.bracketbox span[data-round=' + opt.$trigger.data("round") + ']').each((i, ele) => {
                                if (ele.textContent == opts) {
                                    let confirm_result = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");

                                    if (confirm_result == true) {
                                        updateBracket(opt.$trigger, { name: opts, index: index });
                                    }
                                }
                            });
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
                            url: apiURL + '/bracket/delete/' + element_id,
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
            url: apiURL + '/bracket/save-list',
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
            url: apiURL + '/bracket/',
            success: function(result) {
                if (result.length > 0) {
                    bracketCount = result.length;
                    brackets = result;

                    $('#team-list').addClass('hidden');
                    drawBrackets();                    
                    $('#brackets-box').removeClass('hidden');
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
            type: "GET",
            url: apiURL + '/bracket/clear',
            success: function(result) {
                alert("Brackets was cleared successfully.");

                eleminationType = "Single";
                brackets = [];
                bracketCount = 0;
                $('#brackets').html('');
                drawBrackets ()
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
            type: "GET",
            url: apiURL + '/bracket/clear',
            success: function(result) {
                alert("Brackets was cleared successfully.");

                eleminationType = "Double";
                brackets = [];
                bracketCount = 0;
                $('#brackets').html('');
                drawBrackets ()
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
            url: apiURL + '/bracket/clear',
            success: function(result) {
                alert("Brackets was cleared successfully.");

                $('#team-list').removeClass('hidden');
                $('#brackets-box').addClass('hidden');
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
                updateBracket(ele.parent(), { name: ele.find("option:selected").text(), index: index });
            }
        }
    });
}

function updateBracket(element, data) {
    $("#overlay").fadeIn(300);

    $.ajax({
        type: "put",
        url: apiURL + '/bracket/update/'+element.data('bracket'),
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