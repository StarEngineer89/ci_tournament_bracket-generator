let brackets = [];

let eleminationType = "Single";
let editing_mode = false;

    /*
     * Inject our brackets
     */
    function renderBrackets(struct, direction = 'ltr') {
        var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;
        
        var html = ''
        var minWidth = 240 * groupCount

        // tournament type (3) is Knockout bracket
        if (parseInt(tournament.type) !== KNOCKOUT_TOURNAMENT_TYPE) {
            minWidth = minWidth + 150
        }

        if (direction == 'rtl') {
            html = `<div class="groups group${groupCount} d-flex flex-row-reverse rtl" style="min-width:${minWidth}px"></div>`
        } else {
            html = `<div class="groups group${groupCount} d-flex" style="min-width:${minWidth}px"></div>`
        }
        var group = $(html),
            grouped = _.groupBy(struct, function (s) { return s.roundNo; });

        for (g = 1; g <= groupCount; g++) {
            var round = $('<div class="round r' + g + '"></div>');
            if (parseInt(tournament.type) !== KNOCKOUT_TOURNAMENT_TYPE && g == groupCount) {
                round = $('<div class="round r' + g + '" style="min-width: 350px"></div>');
            }
            
            let timerIcon = ''
            if (parseInt(tournament.availability) && (parseInt(tournament.round_duration_combine) || (parseInt(tournament.evaluation_method) == evaluationMethodVotingCode && parseInt(tournament.voting_mechanism) == votingMechanismRoundDurationCode))) {
                let roundDuration = $(`<div class="round-duration-wrapper text-center p-2 m-2 d-none"></div>`)
                let roundStart = `<strong>Start:</strong> <span class="start">${grouped[g][0].start}</span>`
                let roundEnd = `<strong>End :</strong> <span class="end">${grouped[g][0].end}</span>`

                roundDuration.html(`${roundStart}<br/>${roundEnd}<br/><span class="remaining">&nbsp;</span>`)
                round.append(roundDuration)

                timerIcon = '<button type="button" class="timerTrigger btn btn-light p-0" data-bs-toggle="popover" data-bs-placement="top"><span class="fa-solid fa-clock"></span></button>'
            }

            let editIcon = ''
            if (hasEditPermission) {
                editIcon = `<span class="fa fa-pencil" onclick="enableChangeRoundName(event)"></span>`
            }
            
            let roundName = $(`<div class="round-name-wrapper text-center p-2 m-1 border" style="height: auto" data-round-no="${g}" ${parseInt(grouped[g][0].is_double) ? 'data-knockout-second="true"' : ''}></div>`)
            let round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}`
            if (grouped[g][0].final_match && grouped[g][0].final_match !== "0") {
                round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}: Grand Final`
            }

            roundName.html(`<span class="round-name">${round_name}</span> ${editIcon} ${timerIcon}`)
            round.append(roundName)

            var bracketBoxList = $('<div class="bracketbox-list"></div>')

            _.each(grouped[g], function (gg) {
                var teamwrapper = document.createElement('div')
                teamwrapper.className = "participants"

                var teama = drawParticipant(gg, 0, direction);
                var teamb = drawParticipant(gg, 1, direction);
                var teams = JSON.parse(gg.teamnames);
                var lastGames = JSON.parse(gg.lastGames)
                if (!lastGames) {
                    lastGames = []
                }

                var bracket = document.createElement('div')

                var bracketBorder = document.createElement('div')
                bracketBorder.className = "bracket-border-line"
                bracket.append(bracketBorder)
                
                if (parseInt(gg.final_match) || !isArray(lastGames)) {
                    if (parseInt(tournament.type) == KNOCKOUT_TOURNAMENT_TYPE && !isArray(lastGames)) {
                        bracket.className = "bracketbox knockout-semi-final"
                    } else {
                        bracket.className = "bracketbox final"
                    }
                    // bracket.className = "bracketbox final"
                    
                    teama.className = (teams[0] && gg.final_match) ? "bracket-team teama winner" : teama.className;
                } else {
                    var bracketNo = document.createElement('span')
                    bracketNo.classList.add('bracketNo')
                    bracketNo.innerHTML = gg.bracketNo
                    bracket.append(bracketNo)
                    bracket.className = "bracketbox d-flex align-items-center";
                }

                teamwrapper.append(teama);

                if (!(parseInt(gg.final_match) || !isArray(lastGames))) {
                    teamwrapper.append(teamb)
                }

                if (parseInt(tournament.type) != KNOCKOUT_TOURNAMENT_TYPE && parseInt(gg.final_match)) {
                    let trophy = document.createElement('div')
                    trophy.className = "trophy d-flex align-content-between justify-content-center flex-wrap d-none"
                    trophy.style.minHeight = '100px'
                    bracket.append(trophy)

                    $(trophy).append(`<img src="/images/trophy.png" height="150px" width="150px"/>`)
                    
                    var svg = drawChampionTextSVG()
                    $(trophy).append(`<div class="champion-text animate">${svg}</div>`)

                    if (parseInt(gg.winner)) {
                        trophy.classList.remove('d-none')
                    }
                }

                bracket.append(teamwrapper)

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
                                name: (!isWinner) ? "🏆 Mark as Winner" : "❌ Unmark as winner",
                                callback: (key, opt, e) => {
                                    if (!isWinner)
                                        markWinner(key, opt, e)
                                    else
                                        unmarkWinner(key, opt, e)
                                },
                        }
                    }

                    items.change = {
                                name: "✏️ Change participant",
                                callback: (key, opt, e) => {
                                    const element = opt.$trigger;
                                    $.ajax({
                                        type: "GET",
                                        url: apiURL + '/tournaments/' + tournament_id + '/get-participants',
                                        success: function (result) {
                                            let originalWrapper = document.createElement('div')
                                            originalWrapper.classList.add('original-wrapper', 'd-none')
                                            originalWrapper.innerHTML = element.html()

                                            element.contents().remove();
                                            element.append(originalWrapper)

                                            var select = document.createElement('select');
                                            select.setAttribute('id', "participantSelector");
                                            select.setAttribute('class', "form-control");
                                            var index = (element.hasClass("teama")) ? 0 : 1;

                                            if (result.participants.length > 0) {
                                                let group_ids = []
                                                result.participants.forEach((participant, i) => {
                                                    let pt_group
                                                    if (participant.group_id) {
                                                        if (group_ids.includes(participant.group_id)) {
                                                            return
                                                        }

                                                        pt_group = true
                                                        group_ids.push(participant.group_id)
                                                    }

                                                    var option = document.createElement('option');
                                                    option.setAttribute('value', participant.id);
                                                    option.textContent = participant.name;
                                                    if (participant.id == element.data('id')) {
                                                        option.setAttribute('selected', true)
                                                    }

                                                    if (pt_group) {
                                                        option.setAttribute('data-group', true)
                                                    }

                                                    select.appendChild(option);
                                                });

                                                var saveBtn = document.createElement('button')
                                                saveBtn.setAttribute('class', 'btn btn-primary p-1')
                                                saveBtn.setAttribute('onclick', `changeParticipant($('#participantSelector'), ${index})`)
                                                saveBtn.textContent = 'Save'
                                                
                                                var cancelBtn = document.createElement('button')
                                                cancelBtn.textContent = 'Cancel'
                                                cancelBtn.setAttribute('class', 'btn btn-secondary p-1')
                                                cancelBtn.setAttribute('onClick', 'cancelEditing(this)')
                                                
                                                var elementGroup = document.createElement('div')
                                                elementGroup.setAttribute('class', 'input-group')
                                                elementGroup.append(select)
                                                elementGroup.append(saveBtn)
                                                elementGroup.append(cancelBtn)

                                                element.append(elementGroup)

                                                editing_mode = true;
                                            } else {
                                                alert("There is no participants to be selected");
                                            }

                                            $(select).select2({
                                                width: 115
                                            })

                                            $('.select2-search input').atwho({
                                                at: "@",
                                                searchKey: 'username',
                                                data: initialUsers,
                                                limit: 5, // Show only 5 suggestions
                                                displayTpl: "<li data-value='@${id}'>${username}</li>",
                                                insertTpl: "@${username},",
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
                                                            success: function(data) {
                                                                callback(data);
                                                            }
                                                        });
                                                    }
                                                }
                                            });
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
                    if (!$triggerElement.attr('data-id')) {
                        items.create = {
                            name: "➕ Add participant",
                            callback: (key, opt, e) => {
                                var index = (opt.$trigger.hasClass("teama")) ? 0 : 1;
                                var originalInput = document.getElementById('newParticipantNameInput')
                                if (originalInput) {
                                    originalInput.parentElement.remove()
                                }

                                var inputElement = document.createElement('input')
                                inputElement.setAttribute('class', "form-control form-control-sm")
                                inputElement.setAttribute('id', "newParticipantNameInput")
                                inputElement.focus()
                                $(inputElement).atwho({
                                    at: "@",
                                    searchKey: 'username',
                                    data: initialUsers,
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

                                var buttonElement = document.createElement('button')
                                buttonElement.setAttribute('class', 'btn btn-primary')
                                buttonElement.setAttribute('onclick', `saveNewParticipant($('#newParticipantNameInput'), ${index})`)
                                buttonElement.textContent = 'Save'
                                
                                var cancelBtn = document.createElement('button')
                                cancelBtn.textContent = 'Cancel'
                                cancelBtn.classList.add('btn', 'btn-secondary')
                                cancelBtn.setAttribute('onClick', 'cancelEditing(this)')

                                var elementGroup = document.createElement('div')
                                elementGroup.setAttribute('class', 'input-group input-group-sm')
                                elementGroup.appendChild(inputElement)
                                elementGroup.appendChild(buttonElement)
                                elementGroup.append(cancelBtn)

                                opt.$trigger.append(elementGroup)
                            }
                        }
                    }
                    if ($triggerElement.attr('data-id')) {
                        items.remove = {
                            name: "➖️ Remove participant",
                            callback: (key, opt, e) => {
                                const element = opt.$trigger;
                                var index = (element.hasClass("teama")) ? 0 : 1;
                                    
                                updateBracket(opt.$trigger, { index: index, action_code: removeParticipantActionCode });
                            }
                        }
                    }
                    items.delete = {
                                name: "🗑️ Delete Bracket",
                                callback: (key, opt, e) => {
                                    var element_id = opt.$trigger.data('bracket');
                                    let triggerElement = opt.$trigger
                                    $.ajax({
                                        type: "delete",
                                        url: apiURL + '/brackets/delete/' + element_id,
                                        success: function (result) {
                                            loadBrackets()

                                            // triggerElement.parent().parent().remove();
                                            ws.send(['Deleted Brackets!', tournament_id]);
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

        return group
    }
    
function renderFFABrackets ( result, direction = 'ltr' )
{
    let struct = result.rounds
    let advanceStatus = result.advanceStatus
    var groupCount = _.uniq(_.map(struct, function (s) { return s.roundNo; })).length;

    var html = ''
    var roundNameHtml = ''
    var minWidth = 240 * groupCount

    // tournament type (3) is Knockout bracket
    if (parseInt(tournament.type) !== KNOCKOUT_TOURNAMENT_TYPE) {
        minWidth = minWidth + 150
    }

    if (direction == 'rtl') {
        html = `<div class="groups d-flex align-items-center flex-row-reverse rtl" style="min-width:${minWidth}px"></div>`
        roundNameHtml = `<div class="round-names d-flex flex-row-reverse rtl" style="min-width:${minWidth}px"></div>`
    } else {
        html = `<div class="groups d-flex align-items-center" style="min-width:${minWidth}px"></div>`
        roundNameHtml = `<div class="round-names d-flex" style="min-width:${minWidth}px"></div>`
    }
    var group = $(html),
        grouped = _.groupBy(struct, function (s) { return s.roundNo; });
    
    var roundNames = $(roundNameHtml)

    for (g = 1; g <= groupCount; g++) {
        var round = $('<div class="round r' + g + '"></div>');
        if (parseInt(tournament.type) !== KNOCKOUT_TOURNAMENT_TYPE && g == groupCount) {
            round = $('<div class="round r' + g + '" style="min-width: 350px"></div>');
        }
        
        /** Define the timer icon */
        let timerIcon = ''
        if (parseInt(tournament.availability) && (parseInt(tournament.round_duration_combine) || (parseInt(tournament.evaluation_method) == evaluationMethodVotingCode && parseInt(tournament.voting_mechanism) == votingMechanismRoundDurationCode))) {
            let roundDuration = $(`<div class="round-duration-wrapper text-center p-2 m-2 d-none"></div>`)
            let roundStart = `<strong>Start:</strong> <span class="start">${grouped[g][0].start}</span>`
            let roundEnd = `<strong>End :</strong> <span class="end">${grouped[g][0].end}</span>`

            roundDuration.html(`${roundStart}<br/>${roundEnd}<br/><span class="remaining">&nbsp;</span>`)
            round.append(roundDuration)

            timerIcon = '<button type="button" class="timerTrigger btn btn-light p-0" data-bs-toggle="popover" data-bs-placement="top"><span class="fa-solid fa-clock"></span></button>'
        }

        /** Define the rename icon of round name */
        let editIcon = ''
        if (hasEditPermission) {
            editIcon = `<span class="fa fa-pencil" onclick="enableChangeRoundName(event)"></span>`
        }
        
        let roundName = $(`<div class="round-name-wrapper text-center p-2 m-1 border" style="height: auto" data-round-no="${g}" ${parseInt(grouped[g][0].is_double) ? 'data-knockout-second="true"' : ''}></div>`)
        let round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}`
        if (grouped[g][0].final_match && grouped[g][0].final_match !== "0") {
            round_name = (grouped[g][0].round_name) ? grouped[g][0].round_name : `Round ${grouped[g][0].roundNo}: Grand Final`
        }

        roundName.html( `<span class="round-name">${ round_name }</span> ${ editIcon } ${ timerIcon }` )

        const roundStatus = document.createElement( 'div' )
        roundStatus.className = "roundStatus mt-2"
        roundName.append(roundStatus)

        /** Define the status bar */
        if ( advanceStatus && advanceStatus.round == g )
        {
            const progressContainer = document.createElement( 'div' );
            progressContainer.id = "progressContainer"
            roundStatus.append(progressContainer)
            
            const progressDiv = document.createElement('div');
            progressDiv.className = 'progress';
            
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
            progressBar.setAttribute('role', 'progressbar');
            progressBar.setAttribute('aria-valuenow', '0');
            progressBar.setAttribute('aria-valuemin', '0');
            progressBar.setAttribute('aria-valuemax', '100');
            progressBar.style.width = '0%';
            
            progressDiv.appendChild(progressBar);
            progressContainer.appendChild( progressDiv );
            
            let progressTextEle = document.createElement( 'span' )
            progressTextEle.textContent = `${ advanceStatus.count } of ${ advanceStatus.max } advancing participants selected`
            progressContainer.appendChild(progressTextEle)
            
            let progress = 0;
            let progressTo = advanceStatus.count / advanceStatus.max
            const interval = setInterval(function() {
                progress += 5;
                if (progress > progressTo.toFixed(2) * 100) {
                    clearInterval(interval);
                    return;
                }
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            }, 300);
        }

        /** Update the round status */        
        const badgeCompleted = '<div class="text-bg-secondary">Closed</div>'
        const badgeAwaiting = '<div class="text-bg-danger">Closed: Awaiting Host Decision</div>'

        const currentTime = new Date()
        const startTime = new Date(grouped[g][0].start)
        const endTime = new Date(grouped[g][0].end)
        if ( currentTime > endTime )
        {
            if ( parseInt(grouped[g][0].status) == 2 )
            {
                roundStatus.innerHTML = badgeAwaiting
            } else
            {
                roundStatus.innerHTML = badgeCompleted
            }
        }

        // Add the button start the time manually
        if ( (tournament.timer_start_option == 'm' && grouped[g][0].timer_start == undefined) && (endTime > currentTime && currentTime >= startTime) )
        {
            const timerStartBtn = document.createElement( 'button' )
            timerStartBtn.className = "btn btn-primary btn-sm"
            timerStartBtn.textContent = "Start timer"
            timerStartBtn.addEventListener( 'click', () =>
            {
                const roundNo = event.target.parentElement.parentElement.dataset.roundNo
                $.ajax({
                    type: "POST",
                    url: apiURL + '/brackets/start-round-time',
                    data: {'tournament_id': parseInt(tournament.id), 'round_no': roundNo},
                    success: function (result) {
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
            } )
            
            roundStatus.append(timerStartBtn)
        }

        // Add the button to approve the advancing
        if ( grouped[g][0].status == 2 || (tournament.round_advance_method !== 'a' && (currentTime >= startTime && currentTime < endTime)) )
        {
            const approveBtn = document.createElement( 'button' )
            approveBtn.className = "btn btn-success btn-sm"
            approveBtn.textContent = "Approve Advancing"
            approveBtn.addEventListener( 'click', () =>
            {
                const roundNo = event.target.parentElement.parentElement.dataset.roundNo
                $.ajax({
                    type: "POST",
                    url: apiURL + '/brackets/approve-round',
                    data: {'tournament_id': parseInt(tournament.id), 'round_no': roundNo},
                    success: function (result) {
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
            } )
            
            roundStatus.append(approveBtn)
        }

        /** Add the round names */
        roundNames.append(roundName)

        var bracketBoxList = $('<div class="bracketbox-list"></div>')

        _.each(grouped[g], function (gg) {
            var teamwrapper = document.createElement('div')
            teamwrapper.className = "participants"

            var teams = JSON.parse(gg.teamnames);
            teams.forEach( ( team, i ) => {
                const participant = drawParticipant(gg, i, direction)
                teamwrapper.append( participant )
                
                if ( gg.status == 2 && !team.ranking )
                {
                    participant.classList.add('host-required')
                }
            })
            var lastGames = JSON.parse(gg.lastGames)
            if (!lastGames) {
                lastGames = []
            }

            var bracket = document.createElement('div')

            var bracketBorder = document.createElement('div')
            bracketBorder.className = "bracket-border-line"
            bracket.append(bracketBorder)
            
            if (parseInt(gg.final_match) || !isArray(lastGames)) {
                bracket.className = "bracketbox final"
            } else {
                var bracketNo = document.createElement('span')
                bracketNo.classList.add('bracketNo')
                bracketNo.innerHTML = gg.bracketNo
                bracket.append(bracketNo)
                bracket.className = "bracketbox d-flex align-items-center";
            }
            
            if (parseInt(tournament.type) != KNOCKOUT_TOURNAMENT_TYPE && parseInt(gg.final_match)) {
                let trophy = document.createElement('div')
                trophy.className = "trophy d-flex align-content-between justify-content-center flex-wrap d-none"
                trophy.style.minHeight = '100px'
                bracket.append(trophy)

                $(trophy).append(`<img src="/images/trophy.png" height="150px" width="150px"/>`)
                
                var svg = drawChampionTextSVG()
                $(trophy).append(`<div class="champion-text animate">${svg}</div>`)

                if (parseInt(gg.winner)) {
                    trophy.classList.remove('d-none')
                }
            }

            bracket.append(teamwrapper)

            bracketBoxList.append(bracket);
        });

        round.append(bracketBoxList)

        group.append(round);
    }

    if (hasEditPermission) {
        $.contextMenu({
            selector: '.bracket-team',
            build: function ($triggerElement, e) {
                let isWinner = ( $triggerElement.hasClass( 'winner' ) ) ? true : false;
                
                if ( isWinner )
                {
                    return false
                }

                let items = {}
                items.mark = {
                    name: "🏆 Set Ranking",
                    callback: (key, opt, e) => {
                        setRanking(key, opt, e)
                    },
                }

                if ( manageMetricsPermission && ( manageMetricsTarget == 'all' || manageMetricsTarget == $triggerElement.attr( 'data-id' ) ) )
                {
                    let allowMetricsEdit = false;

                    const currentTime = new Date()
                    const startTime = new Date($triggerElement.data('start'))
                    const endTime = new Date( $triggerElement.data('end') )
                    
                    if ( (endTime > currentTime && currentTime >= startTime) || (parseInt(tournament.round_score_editing) == 1 && currentTime >= endTime) )
                    {
                        allowMetricsEdit = true
                    }



                    if ( allowMetricsEdit )
                    {
                        items.metrics = {
                            name: "🏆 Set the metrics",
                            callback: ( key, opt, e ) =>
                            {
                                setMetrics( key, opt, e )
                            },
                        }
                    }
                }

                items.change = {
                    name: "✏️ Change participant",
                    callback: (key, opt, e) => {
                        const element = opt.$trigger;
                        $.ajax({
                            type: "GET",
                            url: apiURL + '/tournaments/' + tournament_id + '/get-participants',
                            success: function ( result )
                            {
                                let bracketBox = element.parent();
                                let children = bracketBox.children().toArray();
                                let index = children.indexOf( element[0] );
                                
                                let originalWrapper = document.createElement('div')
                                originalWrapper.classList.add('original-wrapper', 'd-none')
                                originalWrapper.innerHTML = element.html()

                                element.contents().remove();
                                element.append(originalWrapper)

                                var select = document.createElement('select');
                                select.setAttribute('id', "participantSelector");
                                select.setAttribute('class', "form-control");

                                if (result.participants.length > 0) {
                                    let group_ids = []
                                    result.participants.forEach((participant, i) => {
                                        let pt_group
                                        if (participant.group_id) {
                                            if (group_ids.includes(participant.group_id)) {
                                                return
                                            }

                                            pt_group = true
                                            group_ids.push(participant.group_id)
                                        }

                                        var option = document.createElement('option');
                                        option.setAttribute('value', participant.id);
                                        option.textContent = participant.name;
                                        if (participant.id == element.data('id')) {
                                            option.setAttribute('selected', true)
                                        }

                                        if (pt_group) {
                                            option.setAttribute('data-group', true)
                                        }

                                        select.appendChild(option);
                                    });

                                    var saveBtn = document.createElement('button')
                                    saveBtn.setAttribute('class', 'btn btn-primary p-1')
                                    saveBtn.setAttribute('onclick', `changeParticipant($('#participantSelector'), ${index})`)
                                    saveBtn.textContent = 'Save'
                                    
                                    var cancelBtn = document.createElement('button')
                                    cancelBtn.textContent = 'Cancel'
                                    cancelBtn.setAttribute('class', 'btn btn-secondary p-1')
                                    cancelBtn.setAttribute('onClick', 'cancelEditing(this)')
                                    
                                    var elementGroup = document.createElement('div')
                                    elementGroup.setAttribute('class', 'input-group')
                                    elementGroup.append(select)
                                    elementGroup.append(saveBtn)
                                    elementGroup.append(cancelBtn)

                                    element.append(elementGroup)

                                    editing_mode = true;
                                } else {
                                    alert("There is no participants to be selected");
                                }

                                $(select).select2({
                                    width: 115
                                })

                                $('.select2-search input').atwho({
                                    at: "@",
                                    searchKey: 'username',
                                    data: initialUsers,
                                    limit: 5, // Show only 5 suggestions
                                    displayTpl: "<li data-value='@${id}'>${username}</li>",
                                    insertTpl: "@${username},",
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
                                                success: function(data) {
                                                    callback(data);
                                                }
                                            });
                                        }
                                    }
                                });
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
                if (!$triggerElement.attr('data-id')) {
                    items.create = {
                        name: "➕ Add participant",
                        callback: ( key, opt, e ) =>
                        {
                            let bracketBox = opt.$trigger.parent();
                            let children = bracketBox.children().toArray();
                            let index = children.indexOf( opt.$trigger[0] );
                            
                            var originalInput = document.getElementById('newParticipantNameInput')
                            if (originalInput) {
                                originalInput.parentElement.remove()
                            }

                            var inputElement = document.createElement('input')
                            inputElement.setAttribute('class', "form-control form-control-sm")
                            inputElement.setAttribute('id', "newParticipantNameInput")
                            inputElement.focus()
                            $(inputElement).atwho({
                                at: "@",
                                searchKey: 'username',
                                data: initialUsers,
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

                            var buttonElement = document.createElement('button')
                            buttonElement.setAttribute('class', 'btn btn-primary')
                            buttonElement.setAttribute('onclick', `saveNewParticipant($('#newParticipantNameInput'), ${index})`)
                            buttonElement.textContent = 'Save'
                            
                            var cancelBtn = document.createElement('button')
                            cancelBtn.textContent = 'Cancel'
                            cancelBtn.classList.add('btn', 'btn-secondary')
                            cancelBtn.setAttribute('onClick', 'cancelEditing(this)')

                            var elementGroup = document.createElement('div')
                            elementGroup.setAttribute('class', 'input-group input-group-sm')
                            elementGroup.appendChild(inputElement)
                            elementGroup.appendChild(buttonElement)
                            elementGroup.append(cancelBtn)

                            opt.$trigger.append(elementGroup)
                        }
                    }
                }
                if ($triggerElement.attr('data-id')) {
                    items.remove = {
                        name: "➖️ Remove participant",
                        callback: (key, opt, e) => {
                            let bracketBox = opt.$trigger.parent();
                            let children = bracketBox.children().toArray();
                            let index = children.indexOf( opt.$trigger[0] );
                                
                            updateBracket(opt.$trigger, { index: index, action_code: removeParticipantActionCode });
                        }
                    }
                }
                items.delete = {
                    name: "🗑️ Delete Bracket",
                    callback: (key, opt, e) => {
                        var element_id = opt.$trigger.data('bracket');
                        let triggerElement = opt.$trigger
                        $.ajax({
                            type: "delete",
                            url: apiURL + '/brackets/delete/' + element_id,
                            success: function (result) {
                                loadBrackets()

                                // triggerElement.parent().parent().remove();
                                ws.send(['Deleted Brackets!', tournament_id]);
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

    $('#brackets').html(roundNames)
    $( '#brackets' ).append( group )
    
    if ( advanceStatus )
    {
        const statusDiv = $('<div>', {
            class: 'advancing-status',
            text: '⚙️ Waiting for all top participants to be selected before creating next round brackets…'
        });
        $('#brackets').prepend(statusDiv)
    }
}
    
    function drawParticipant(bracket, team_index = 0, direction = 'ltr') {
        let round_no = bracket.roundNo
        var participant = document.createElement('span');
        participant.dataset.order = bracket.bracketNo;
        participant.dataset.bracket = bracket.id;
        participant.dataset.next = bracket.nextGame;
        participant.dataset.round = bracket.roundNo;
        participant.textContent = ' ';

        if ( bracket.start !== undefined )
        {
            participant.dataset.start = bracket.start
        }
        
        if ( bracket.end !== undefined )
        {
            participant.dataset.end = bracket.end
        }

        if ( bracket.timer_start !== undefined )
        {
            participant.dataset.timerStart = bracket.timer_start
        }

        var pidBox = document.createElement('span')
        pidBox.classList.add('p-id')

        var teams = JSON.parse(bracket.teamnames);
        participant.className = 'bracket-team teama';
        if (team_index == 0) {
            participant.className = 'bracket-team teama';
        } else {
            participant.className = 'bracket-team teamb';
        }

        if (tournament.type == 4) {
            participant.className = "bracket-team"
        }

        participant.classList.add('d-flex')
        if (direction == 'rtl') {
            participant.classList.add('flex-row-reverse')
        }
        
        if (teams[team_index] != undefined) {
            var pid = pidBox.cloneNode(true)
            pid.textContent = parseInt(teams[team_index].order) + 1
            participant.appendChild(pid)
            
            if (teams[team_index].image) {
                $(participant).append(`<div class="p-image d-flex"><img src="${teams[team_index].image}" height="30px" width="30px" class="parect-cover" id="pimage_${teams[team_index].id}" data-pid="${teams[team_index].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[team_index].id})" name="image_${teams[team_index].id}" id="image_${teams[team_index].id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${teams[team_index].id})"><i class="fa fa-trash-alt"></i></button></div>`);
            } else {
                $(participant).append(`<div class="p-image d-flex"><img src="/images/avatar.jpg" height="30px" width="30px" class="temp object-cover" id="pimage_${teams[team_index].id}" data-pid="${teams[team_index].id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${teams[team_index].id})" name="image_${teams[team_index].id}" id="image_${teams[team_index].id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${teams[team_index].id})"><i class="fa fa-trash-alt"></i></button></div>`)
            }

            participant.dataset.id = teams[team_index].id;
            participant.dataset.p_order = teams[team_index].order;
            var nameSpan = document.createElement('span')
            nameSpan.classList.add('name')
            nameSpan.setAttribute('data-bs-toggle', "tooltip")
            if (teams[team_index].email) {
                nameSpan.setAttribute('data-bs-html', true)
                nameSpan.setAttribute('data-bs-title', teams[team_index].name + `<br/>(${teams[team_index].email})`)
            } else if (parseInt(teams[team_index].is_group)) {
                participant.dataset.isGroup = true
                nameSpan.setAttribute('data-bs-html', true)
                let members = ''
                teams[team_index].members.forEach(member => {
                    members += `${member.name}<br/> `
                })
                nameSpan.setAttribute('data-bs-title', `Group: ${teams[team_index].name}<br/>Members:<br/>${members}`)
            } else {
                nameSpan.setAttribute('data-bs-title', teams[team_index].name)
            }
            nameSpan.textContent = teams[team_index].name;
            participant.appendChild(nameSpan)

            if (teams[team_index].id == bracket.winner) {
                participant.classList.add('winner');
            }

            var wrapper = document.createElement('span')
            wrapper.classList.add('score-wrapper')
            wrapper.classList.add('d-flex')
            if (direction == 'rtl') {
                wrapper.classList.add('flex-row-reverse')
            }

            if (isScoreEnabled) {
                var score = document.createElement('span')
                score.classList.add('score')
                var scorePoint = 0

                if ( tournament.scoring_method == "d" && tournament.score_manual_override ) // "d": System defined scoring
                {
                    let is_final_match = false
                    if ( parseInt( tournament.type ) == 3 )
                    {
                        is_final_match = ( parseInt( bracket.knockout_final ) ) ? true : false;
                    } else
                    {
                        is_final_match = ( parseInt( bracket.final_match ) ) ? true : false;
                    }
                
                    if ( incrementScoreType == 'p' )
                    {
                        for ( round_i = 0; round_i < round_no - 1; round_i++ )
                        {
                            scorePoint += scoreBracket
                            scorePoint += incrementScore * round_i
                        }

                        if ( !is_final_match && teams[team_index].id == bracket.winner )
                        {
                            scorePoint += scoreBracket
                            scorePoint += incrementScore * ( round_no - 1 )
                        }
                    } else
                    {
                        scorePoint += scoreBracket
                        if ( round_no == 1 && teams[team_index].id !== bracket.winner )
                        {
                            scorePoint = 0
                        }

                        for ( round_i = 0; round_i < round_no - 2; round_i++ )
                        {
                            scorePoint += scorePoint * incrementScore
                        }
                    
                        if ( !is_final_match && round_no > 1 && teams[team_index].id == bracket.winner )
                        {
                            scorePoint += scorePoint * incrementScore
                        }
                    }
                } else
                {
                    scorePoint = teams[team_index].score ?? scorePoint;
                }
            
                score.textContent = scorePoint
                wrapper.appendChild(score)
            }
            
            if (votingEnabled) {
                var votes = document.createElement('span')
                votes.classList.add('votes')
                if (tournament.vote_displaying == 'n') {
                    votes.textContent = teams[team_index].votes ? teams[team_index].votes : 0
                } else {
                    let totalVotes = 0;
                    if (teams[0]) totalVotes += parseInt(teams[0].votes)
                    if (teams[1]) totalVotes += parseInt(teams[1].votes)
                    votes.textContent = teams[team_index].votes ? Math.round(teams[team_index].votes/totalVotes * 100) + '%' : 0 + '%'
                }
                // Set up the tooltip with HTML content (a button)
                wrapper.appendChild(votes)

                // Check if vote history is existing
                let storage_key = 'vote_t' + tournament_id + '_n' + bracket.roundNo + '_b' + bracket.id
                if (parseInt(tournament.type) == 3 && parseInt(bracket.final_match)) {
                    storage_key = 'vote_t' + tournament_id + '_n' + bracket.roundNo + '_b' + bracket.next_id
                }

                let vp_id = window.localStorage.getItem(storage_key)
                if (vp_id && vp_id == teams[team_index].id) {
                    teams[team_index].voted = true
                }

                if (parseInt(tournament.type) == 3 && bracket.knockout_final) {
                    teams[team_index].voted = true
                }
                
                let voteBtnAvailable = voteActionAvailable
                if (parseInt(bracket.win_by_host) || teams[team_index].voted) {
                    voteBtnAvailable = false
                }
                if ((parseInt(tournament.type) != 3 && parseInt(bracket.final_match) == 1) || (parseInt(tournament.type) == 3 && parseInt(bracket.knockout_final) == 1)) {
                    voteBtnAvailable = false
                }
                if ( (votingMechanism == votingMechanismMaxVoteCode) && !(maxVoteCount > 0 && teams[team_index].votes_in_round < maxVoteCount) ) {
                    voteBtnAvailable = false
                }

                if (voteBtnAvailable) {
                    var voteBtn = document.createElement('button')
                    voteBtn.classList.add('vote-btn')
                    voteBtn.dataset.id = participant.dataset.bracket
                    voteBtn.addEventListener('click', (event) => {
                        submitVote(event)
                    })
                    
                    var voteBtnIcon = document.createElement('span')
                    voteBtnIcon.classList.add('fa')
                    voteBtnIcon.classList.add('fa-plus')
                    voteBtn.appendChild(voteBtnIcon)

                    wrapper.appendChild(voteBtn)
                }
            }

            if ( parseInt( tournament.type ) == 4 && !bracket.final_match)
            {
                var rankingSpan = document.createElement('span')
                rankingSpan.classList.add('ranking')
                rankingSpan.textContent = teams[ team_index ].ranking ?? "-"
                wrapper.appendChild( rankingSpan )
                
                // Set background color of 1st, 2nd, 3rd ranking
                if ( parseInt( teams[team_index].ranking ) == 1 )
                {
                    participant.classList.add('first')
                }
                if ( parseInt( teams[team_index].ranking ) == 2 )
                {
                    participant.classList.add('second')
                }
                if ( parseInt( teams[team_index].ranking ) == 3 )
                {
                    participant.classList.add('third')
                }
            }
            
            if ( teams[team_index].score )
            {
                participant.dataset.score = teams[team_index].score;
            }
            if ( teams[team_index].time )
            {
                participant.dataset.time = teams[team_index].time;
            }

            participant.appendChild(wrapper)
        }

        return participant
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

    function loadBrackets(confetti = null) {
        $.ajax({
            type: "get",
            url: apiURL + '/tournaments/' + tournament_id + '/brackets?uuid=' + UUID,
            success: function (result) {
                if (result.rounds.length > 0) {
                    if (parseInt(tournament.type) == KNOCKOUT_TOURNAMENT_TYPE) {
                        let list_1 = []
                        let list_2 = []
                        let knockout_final
                        result.rounds.forEach((e, i) => {
                            if (parseInt(e.is_double) == 1) {
                                if (parseInt(e.knockout_final) == 1) {
                                    knockout_final = e
                                } else {
                                    list_2.push(e)
                                }
                            } else {
                                list_1.push(e)
                            }
                        })

                        let left_brackets = renderBrackets(list_1)
                        let left_wrapper = document.createElement('div')
                        left_wrapper.id = "left_wrapper"
                        left_wrapper.appendChild(left_brackets[0])

                        let right_brackets = renderBrackets(list_2, 'rtl')
                        let right_wrapper = document.createElement('div')
                        right_wrapper.id = "right_wrapper"
                        right_wrapper.appendChild(right_brackets[0])

                        let center_wrapper = document.createElement('div')
                        center_wrapper.classList.add('center-wrapper', 'align-self-center')
                        center_wrapper.style.minWidth = '350px'
                        center_wrapper.style.minHeight = '300px'
                        let bracketDiv = document.createElement('div')
                        bracketDiv.classList.add('knockout-final', 'd-flex', 'align-items-end', 'justify-content-center')

                        let trophy = document.createElement('div')
                        trophy.className = "trophy d-flex align-content-between justify-content-center flex-wrap d-none"
                        trophy.style.minHeight = '100px'
                        center_wrapper.append(trophy)
                        
                        $(trophy).append(`<img src="/images/trophy.png" height="150px" width="150px"/>`)
                
                        var svg = drawChampionTextSVG()
                        $(trophy).append(`<div class="champion-text animate">${svg}</div>`)

                        if (knockout_final && knockout_final.winner) {
                            trophy.classList.remove('d-none')
                        }

                        let final_bracket = drawParticipant(knockout_final)
                        bracketDiv.append(final_bracket)
                        center_wrapper.append(bracketDiv)
                        
                        $('#brackets').html('')
                        $('#brackets').append(left_wrapper, center_wrapper, right_wrapper)
                        adjustBracketsStyles(document.getElementById('left_wrapper'))
                        adjustBracketsStyles(document.getElementById('right_wrapper'))
                    } else if (parseInt(tournament.type) == 4) {
                        renderFFABrackets(result);
                    } else {
                        let brackets = renderBrackets(result.rounds);
                        $('#brackets').html(brackets);
                        
                        adjustBracketsStyles(document.getElementById('brackets'))
                    }
                }

                if (confetti) {
                    initConfetti()
                }

                adjustRoundCountdown()
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    }
    
    let saveNewParticipant = (element, index) => {
        let ability = true;
        var name = element.val()
        var participantElement = element.parent().parent()

        if (name) {
            let duplicated = false;
            let force_add = false;

            $('.bracketbox span.bracket-team').each((i, ele) => {
                if (duplicated)
                    return false

                if ($(ele).find('.name').text().toLowerCase() == name.toLowerCase()) {
                    duplicated = true;
                    force_add = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");
                }
            });

            if (!duplicated || force_add) {
                updateBracket(participantElement, { name: name, index: index, action_code: addParticipantActionCode, order: participantElement.data("order") });
            }
        } else
            alert('Please input the name of the participant.');
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
                if (result.result == "success") {
                    ws.send(['updated!', tournament_id]);
                    loadBrackets()
                } else {
                    $('#errorModal .errorDetails').html(result.message)
                    $("#errorModal").modal('show'); 
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

    function markWinner(key, opt, e) {
        let orders = _.uniq(_.map(document.querySelectorAll("[data-next='" + opt.$trigger.data('next') + "']"), function (ele) { return ele.dataset.order }));
        let index = orders.findIndex((value) => { return value == opt.$trigger.data('order') });
        const next_id = opt.$trigger.data('next');
        let next_bracketObj = document.querySelectorAll('[data-order="' + next_id + '"]')[index];
        next_bracket = next_bracketObj.dataset.bracket;

        let is_final = false
        if (next_bracketObj.parentElement.parentElement.classList.contains('final')) {
            is_final = true
        }
        if (parseInt(tournament.type) == 3 && next_bracketObj.parentElement.classList.contains('knockout-final')) {
            is_final = true
        }

        $.ajax({
            type: "PUT",
            url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
            contentType: "application/json",
            data: JSON.stringify({ winner: opt.$trigger.data('id'), order: opt.$trigger.data('p_order'), action_code: markWinnerActionCode, is_final: is_final, index: index, is_group: opt.$trigger.data('isGroup') }),
            success: function (result) {
                let final_win = false

                if (parseInt(tournament.type) == 3) {
                    if (next_bracketObj.parentElement.classList.contains('knockout-final')) {
                        final_win = true
                    }
                } else {
                    if (next_bracketObj.parentElement.parentElement.classList.contains('final')) {
                        final_win = true
                    }
                }
                
                if (final_win) {
                    ws.send(['winnerChange', tournament_id])

                    loadBrackets('initConfetti');

                    next_bracketObj.classList.add('winner');

                    var player = document.getElementById('myAudio');
                    if (player) {
                        player.addEventListener("timeupdate", function () {
                            if ((player.currentTime - player._startTime) >= player.value) {
                                player.pause();
                                document.getElementById('stopAudioButton').classList.add('d-none');
                            };
                        });

                        player.value = player.dataset.duration;
                        player._startTime = player.dataset.starttime;
                        player.currentTime = player.dataset.starttime;
                        player.play();
                    }

                    if (document.getElementById('stopAudioButton')) {
                        document.getElementById('stopAudioButton').classList.remove('d-none');
                        document.getElementById('stopAudioButton').textContent = "Pause Audio"
                    }
                } else {
                    ws.send(['marked!', tournament_id])
                    loadBrackets();
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
        if (next_bracketObj.parentElement.parentElement.classList.contains('final')) {
            is_final = true
        }

        const ele = opt.$trigger;
        $.ajax({
            type: "PUT",
            url: apiURL + '/brackets/update/' + opt.$trigger.data('bracket'),
            contentType: "application/json",
            data: JSON.stringify({ action_code: unmarkWinnerActionCode, participant: opt.$trigger.data('id'), index: index, is_final: is_final, is_group: opt.$trigger.data('isGroup')}),
            success: function (result) {
                ws.send(['unmarked!', tournament_id]);

                loadBrackets()

                if (document.getElementById('stopAudioButton')) {
                    document.getElementById('stopAudioButton').classList.add('d-none');
                    document.getElementById('stopAudioButton').textContent = "Pause Audio"
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

function changeParticipant(ele, index) {
    let ability = true;
    let parentElement = ele.parent().parent()

    if (parentElement.data('id') == ele.val()) {
        confirm(" No changes were made")
        return false
    }

    $('.bracketbox span[data-round=' + parentElement.data("round") + ']').each((i, e) => {
        if (!ability)
            return
        
        if (e.dataset.id == ele.val()) {
            let confirm_result = confirm("This participant already exists in this round's brackets. Are you sure you want to proceed?");

            if (confirm_result == false) {
                ability = false;
                return false;
            }
        }
    });

    if (ability) {
        let participant_order = parentElement.data('order')
        if (!participant_order) {
            participant_order = (parseInt(parentElement.data('order')) - 1) * 2 + index + 1
        }

        editing_mode = false;

        updateBracket(parentElement, { name: ele.find("option:selected").text(), index: index, participant: ele.find("option:selected").val(), action_code: changeParticipantActionCode, order: participant_order, is_group: ele.find("option:selected").data('group') });
    }
}

function adjustBracketsStyles(obj) {
    const rows = obj.querySelectorAll(".brackets div.groups div.bracketbox-list");
    const baseHeight = 30;
    const baseMargin = 40;
    
        let extraMarginTop = 0;
        if (rows[0].querySelectorAll('.bracketbox').length == 1) {
            extraMarginTop = 70
        }
    
    rows.forEach((row, index) => {
        const multiplier = Math.pow(2, index + 1);
        const height = baseHeight * multiplier;
        const margin = baseHeight * Math.pow(2, index) + baseMargin / 2;
        
        row.querySelectorAll('.bracketbox').forEach((bracket, index) => {
            
            if (bracket.classList.contains('final') || bracket.classList.contains('knockout-semi-final')) {
                bracket.style.height = `0`;
                bracket.style.margin = `${margin + extraMarginTop}px 0 0`;
            } else {
                bracket.style.height = `${height}px`;
                if (row.querySelectorAll('.bracketbox').length > index + 1) {
                    bracket.style.margin = `${margin + extraMarginTop}px 0 ${height}px`;
                } else {
                    bracket.style.margin = `${margin + extraMarginTop}px 0`;
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
                if (result.errors) {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

                ws.send(['marked!', tournament_id]);
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
            ws.send(['marked!', tournament_id])
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

function cancelEditing(element) {
    let origin = element.parentElement.parentElement.getElementsByClassName('original-wrapper')[0]
    if (origin) {
        element.parentElement.parentElement.innerHTML = origin.innerHTML
    } else {
        element.parentElement.parentElement.innerHTML = ''
    }
}

let submitVote = (event) => {
    const participant_element = $(event.currentTarget).parents('.bracket-team')
    $.ajax({
        type: "POST",
        url: apiURL + '/tournaments/vote',
        data: {
            'tournament_id': tournament_id,
            'participant_id': participant_element.data('id'),
            'bracket_id': participant_element.data('bracket'),
            'round_no': participant_element.data('round'),
            'is_group': participant_element.data('is-group'),
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

            if (result.data.final_win) {
                // triggerElement.parent().parent().remove();
                ws.send(['winnerChange', tournament_id]);

                loadBrackets('initConfetti');

                var player = document.getElementById('myAudio');
                if (player) {
                    player.addEventListener("timeupdate", function () {
                        if ((player.currentTime - player._startTime) >= player.value) {
                            player.pause();
                            document.getElementById('stopAudioButton').classList.add('d-none');
                        };
                    });

                    player.value = player.dataset.duration;
                    player._startTime = player.dataset.starttime;
                    player.currentTime = player.dataset.starttime;
                    player.play();
                }

                if (document.getElementById('stopAudioButton')) {
                    document.getElementById('stopAudioButton').classList.remove('d-none');
                    document.getElementById('stopAudioButton').textContent = "Pause Audio"
                }
            } else {
                // triggerElement.parent().parent().remove();
                ws.send(['Vote the participant!', tournament_id]);

                loadBrackets();
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
    if (parseInt(tournament.availability) && tournament.evaluation_method == evaluationMethodVotingCode && tournament.voting_mechanism == votingMechanismRoundDurationCode) {
        html += `<button type="button" class="timerTrigger btn btn-light p-0" data-bs-toggle="popover" data-bs-placement="top"><span class="fa-solid fa-clock"></span></button>`
    }
    event.currentTarget.parentElement.parentElement.innerHTML = html
    adjustRoundCountdown()
}

let saveRoundName = (event) => {
    const name = event.currentTarget.value
    $.ajax({
        type: "POST",
        url: apiURL + '/brackets/save-round',
        data: {
            'tournament_id': tournament_id,
            'round_no': event.currentTarget.parentElement.parentElement.dataset.roundNo,
            'round_name': event.currentTarget.parentElement.firstChild.value,
        },
        dataType: "JSON",
        success: function (result) {
            loadBrackets()

            // triggerElement.parent().parent().remove();
            ws.send(['Vote the participant!', tournament_id]);
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

let initConfetti = () => {
    const duration = 10 * 1000,
        animationEnd = Date.now() + duration,
        defaults = { startVelocity: 30, spread: 360, ticks: 20, zIndex: 0 };

    scrollToMiddle(document.getElementById('brackets'));

    if (winnerAudioPlayingForEveryone) {
        var player = document.getElementById('myAudio');
        if (player) {
            player.value = player.dataset.duration;
            player._startTime = player.dataset.starttime;
            player.currentTime = player.dataset.starttime;

            // Add an event listener for user interaction
            document.addEventListener('click', function initPlayback() {
                // Play the audio
                player.play().then(() => {
                    console.log('Audio playback started successfully.');
                }).catch((error) => {
                    console.error('Audio playback failed:', error);
                });

                // Remove the event listener after the first interaction
                document.removeEventListener('click', initPlayback);
            });

            // Add the timeupdate event listener
            player.addEventListener("timeupdate", function () {
                if ((player.currentTime - player._startTime) >= player.value) {
                    player.pause();
                    document.getElementById('stopAudioButton').classList.add('d-none');
                }
            });

            if (document.getElementById('stopAudioButton')) {
                document.getElementById('stopAudioButton').classList.remove('d-none');
                document.getElementById('stopAudioButton').textContent = "Pause Audio"
            }

            var clickEvent = new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            });

            // Dispatch the event on the document
            document.dispatchEvent(clickEvent);
        }
    }

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }
    if ($(document.getElementById('confetti')).length > 0) {
        document.getElementById('confetti').style.display = 'block';
    }
    var interval = setInterval(function () {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            if ($(document.getElementById('confetti')).length > 0) {
                document.getElementById('confetti').style.display = 'none';
            }
            return clearInterval(interval);
        }

        const particleCount = 20 * (timeLeft / duration);

        // since particles fall down, start a bit higher than random
        confetti(
            Object.assign({}, defaults, {
                particleCount,
                origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
            })
        );
        confetti(
            Object.assign({}, defaults, {
                particleCount,
                origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
            })
        );
    }, 250);

    console.log($('.trophy'))
    $('.trophy').addClass('animate')
    console.log($('.trophy'))
}

function scrollToMiddle(element) {
  const container = element.parentElement;

    // Calculate the middle position
    let middle = 0
    if (parseInt(tournament.type) == 3) {
        middle = element.scrollWidth / 2 - container.offsetWidth / 2
    } else {
        middle = element.scrollWidth
    }

  // Scroll to the middle
  element.scrollLeft = middle;
}

let drawChampionTextSVG = () => {
    var svg = `
            <svg viewBox="0 0 1200 325" id="svg">

            <defs>
            <linearGradient id="redGradient">
                <stop offset="0%" stop-color="rgba(255, 0, 0, 0.6)" />
                <stop offset="50%" stop-color="rgba(0, 0, 0, 0.6)" />
            </linearGradient>

            <linearGradient id="yellowGradient" gradientTransform="rotate(90)">
                <stop offset="0%" stop-color="#e1a588" />
                <stop offset="50%" stop-color="#f1e9a7" />
                <stop offset="100%" stop-color="#e1a588" />
            </linearGradient>

            </defs>

            <g id="splines">
            <rect id="spline-1" x="512.5" y="27.5" height="260" width="35" fill="url(#yellowGradient)" />
            <rect id="spline-1" x="582.5" y="0" height="370" width="40" fill="url(#yellowGradient)" />
            <rect id="spline-1" x="652.5" y="27.5" height="260" width="35" fill="url(#yellowGradient)" />
            </g>

            <rect id="banner-1" x="0" y="142.5" height="80" width="1200" fill="rgba(255, 255, 255, 0.3)" />

            <rect id="banner-2" x="150" y="132.5" height="100" width="900" fill="rgba(255, 255, 255, 0.3)" />

            <rect id="banner-3" x="225" y="102.5" height="140" width="750" fill="rgba(0, 0, 0, 0.3)" />

            <rect id="banner-4" x="275" y="110" height="125" width="650" fill="url(#redGradient)" />

            <rect id="banner-5" x="437.5" y="67.5" height="35" width="325" fill="rgba(0, 0, 0, 0.3)" />

            <g fill="rgba(255, 255, 255, 1)" id="line-1">
            <rect id="line-1-1" x="210" y="122" height="4" width="130" fill="rgba(255, 255, 255, 1)" />
            <rect id="line-1-2" x="210" y="126" height="4" width="600" />
            </g>

            <g fill="rgba(255, 255, 255, 1)" id="line-2">
            <rect id="line-2-1" x="390" y="223" height="4" width="600" />
            <rect id="line-2-2" x="860" y="227" height="4" width="130" fill="rgba(255, 255, 255, 1)" />
            </g>

            <text x="50%" y="65%" class="heading" font-size-adjust="1" font-size="48px">CHAMPION</text>

        </svg>
    `
    
    return svg
}

let displayQRCode = () => {
    const currentUrl = window.location.href;
    const qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = '';
    const qrCode = new QRCode(qrContainer, {
            text: currentUrl,     // The URL to encode
            width: 200,           // QR code width
            height: 200,          // QR code height
            colorDark: "#000000", // Dark color
            colorLight: "#ffffff" // Light color
    });
    
    document.getElementById('tournamentURL').value = window.location.href;
    
    $('#displayQRCodeModal').modal('show')
}

let adjustRoundCountdown = () => {
    const popoverTriggerList = document.querySelectorAll('.timerTrigger');
    [...document.getElementsByClassName('round-duration-wrapper')].forEach((obj, i) => {
        if (!popoverTriggerList[i]) {
            console.warn(`Popover trigger not found for index ${i}`);
            return;
        }

        let endTime = new Date(obj.getElementsByClassName('end')[0].textContent);
        let remainingTime;
        
        // Function to update the countdown timer
        function updateCountdown() {
            let now = new Date();
            remainingTime = (endTime - now) / 1000;
            
            if (remainingTime <= 0) {
                obj.getElementsByClassName('remaining')[0].innerHTML = "Completed!";
                return obj.innerHTML;
            } else {
                let days = Math.floor(remainingTime / (60 * 60 * 24));
                let hours = Math.floor((remainingTime % (60 * 60 * 24)) / (60 * 60));
                let minutes = Math.floor((remainingTime % (60 * 60)) / 60);
                let seconds = parseInt(remainingTime % 60);

                let timeString = `${days}d ${hours}h ${minutes}m ${seconds}s`

                obj.getElementsByClassName('remaining')[0].innerHTML = `<strong>Remaining : </strong>${timeString}<br/><span>&nbsp;</span>`

                return obj.innerHTML; // Return for popover
            }
        }

        // Initialize popover with dynamic content update
        let popover = new bootstrap.Popover(popoverTriggerList[i], {
            html: true,
            trigger: 'focus',
            content: function () {
                return `<div id="popover-timer-${i}">${updateCountdown()}</div>`; // Set live timer
            }
        });

        // Update popover content every second
        setInterval(() => {
            let popoverElement = document.getElementById(`popover-timer-${i}`);
            if (popoverElement) {
                popoverElement.innerHTML = updateCountdown();

                if (remainingTime <= 0 && obj.getElementsByClassName('remaining')[0].innerHTML != "Completed!") {
                    adjustRoundCountdown();
                }
            }
        }, 1000);
    });
}

let setRanking = ( key, opt, e ) =>
{
    const bracketId = parseInt( opt.$trigger.data( 'bracket' ) )
    const participantId = parseInt( opt.$trigger.data( 'id' ) )
    const roundNo = parseInt( opt.$trigger.data( 'round' ) )
    
    opt.$trigger.find( '.ranking' ).addClass( 'editing' )
    
    // Create the select element
    const selectElement = document.createElement('select');
    selectElement.className = 'form-select';
    selectElement.name = "ranking"

    // Add options to the select element (you can customize these)
    let options = ['-'];
    let existingRankings = [];
    opt.$trigger.parent().children().each((index, ele) => {
        options.push( index + 1 )
        
        if (ele.querySelector('.ranking') && ele.querySelector('.ranking').textContent != '-' )
        {
            existingRankings.push( parseInt(ele.querySelector('.ranking').textContent) );
        }
    } )
    
    options.forEach(optionValue => {
        const option = document.createElement('option');
        option.value = optionValue;
        option.textContent = optionValue;

        if ( existingRankings.includes( optionValue ) )
        {
            option.disabled = true;
        }

        selectElement.appendChild(option);
    });

    // Find the .ranking span and replace its content with the select element
    const rankingSpan = opt.$trigger.find( '.ranking' )[0];
    if (rankingSpan) {
        rankingSpan.textContent = ''; // Clear existing content
        rankingSpan.appendChild(selectElement);
    }

    selectElement.addEventListener('change', (e) => {
        $.ajax({
            type: "POST",
            url: apiURL + '/brackets/save-ranking',
            data: {'tournament_id': parseInt(tournament.id), 'bracket_id': bracketId, 'participant_id': participantId, 'roundNo': roundNo, 'ranking': parseInt(e.target.value)},
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

                let confettii = null
                if ( typeof result.championSelected !== 'undefined' )
                {
                    confettii = true
                }

                loadBrackets(confettii)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let setMetrics = (key, opt, e) => {
    const bracketId = parseInt(opt.$trigger.data('bracket'));
    const participantId = parseInt(opt.$trigger.data('id'));
    const roundNo = parseInt( opt.$trigger.data( 'round' ) );
    let editingMode = false;
    
    let score = opt.$trigger.data( 'score' )
    let time = opt.$trigger.data( 'time' )
    if ( opt.$trigger.data( 'score' ) == undefined )
    {
        score = null
    }

    if ( opt.$trigger.data( 'time' ) == undefined )
    {
        time = null
    }

    if ( score || time )
    {
        editingMode = true
    }

    const triggerEl = opt.$trigger[0];

    // Remove existing popover if present
    const existing = bootstrap.Popover.getInstance(triggerEl);
    if (existing) {
        existing.dispose();
    }

    // Create a container element
    const content = document.createElement('div');
    content.classList.add('d-flex', 'gap-1', 'align-items-end');

    content.innerHTML = `
        <div style="min-width: 40px">
            <label class="form-label text-center m-0 d-block">Score</label>
            <input type="text" class="form-control" name="score" value="${score ?? ''}">
        </div>
        <div class="flex-grow-1" style="min-width:160px;">
            <label class="form-label text-center m-0 d-block">Time</label>
            <div class="input-group">
                <span class="input-group-text" id="watchIcon">
                    <i class="bi bi-stopwatch"></i>
                </span>
                <input type="text" class="form-control" name="time" value="${time ?? ''}">
            </div>
        </div>
        <!-- Full width Save button on next line -->
        <div class="w-100">
            <button class="btn btn-success w-100" id="saveMetricsBtn"><i class="fa fa-save"></i></button>
        </div>
        `;

    // Create the popover
    const popover = new bootstrap.Popover(triggerEl, {
        content: content,
        html: true,
        placement: 'bottom',
        trigger: 'manual',
        container: 'body'
    });

    // Show the popover
    setTimeout(() => {
        popover.show();

        let timerInterval = null;

        // Start timer
        const timeInput = document.querySelector('.popover-body input[name="time"]');
        const startTime = opt.$trigger.data( 'timerStart' ) ?? opt.$trigger.data( 'start' );
        let seconds = getTimeProgress(startTime);
        let isRunning = true;

        // Helper: format seconds -> xxD hh:mm:ss
        function formatTime(totalSeconds) {
            const days = Math.floor(totalSeconds / (24 * 3600));
            const hours = Math.floor((totalSeconds % (24 * 3600)) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const secs = totalSeconds % 60;

            return `${String(days).padStart(2, '0')}D `
                + `${String(hours).padStart(2, '0')}:`
                + `${String(minutes).padStart(2, '0')}:`
                + `${String(secs).padStart(2, '0')}`;
        }

        // Helper: get time progressed
        function getTimeProgress(startTime)
        {
            // Target datetime (NY time)
            const target = new Date(startTime); 
            // NOTE: -04:00 is Eastern Daylight Time offset in August

            // Get current time in America/New_York
            const now = new Date(
                new Date().toLocaleString("en-US", { timeZone: timezone })
            );

            // Calculate difference in second
            const diffSeconds = Math.floor((now - target) / 1000);
            
            return diffSeconds
        }

        if (timeInput && !editingMode) {
            timerInterval = setInterval(() => {
                seconds += 1;
                timeInput.value = formatTime(seconds);
            }, 1000);
        }

        // Handle stopwatch icon click
        document.getElementById('watchIcon').addEventListener('click', () => {
            if (isRunning) {
                // Stop timer
                clearInterval(timerInterval);
                timerInterval = null;
                isRunning = false;
            } else {
                // Restart timer
                seconds = getTimeProgress(startTime);
                timerInterval = setInterval(() => {
                    seconds++;
                    timeInput.value = formatTime(seconds);
                }, 1000);
                isRunning = true;
            }
        });

        // Handle Save click
        setTimeout(() => {
            const saveBtn = document.getElementById('saveMetricsBtn');
            saveBtn?.addEventListener( 'click', ( event ) =>
            {
                const score = document.querySelector( '.popover-body input[name="score"]' ).value
                const time = document.querySelector( '.popover-body input[name="time"]' ).value

                clearInterval(timerInterval);

                $.ajax({
                    type: "POST",
                    url: apiURL + '/brackets/save-score',
                    data: {'tournament_id': parseInt(tournament.id), 'bracket_id': bracketId, 'participant_id': participantId, 'roundNo': roundNo, 'score': parseInt(score), 'time': time},
                    success: function (result) {
                        if (result.status == 'error') {
                            $('#errorModal .errorDetails').html(result.errors.file)
                            $("#errorModal").modal('show');
        
                            return false
                        }
        
                        loadBrackets()
                        popover.hide();
                    },
                    error: function (error) {
                        console.log(error);
                    }
                }).done(() => {
                    setTimeout(function () {
                        $("#overlay").fadeOut(300);
                    }, 500);
                });
            } );
        }, 10);

        // Hide on outside click
        const onClickOutside = (event) => {
            const popoverEl = document.querySelector('.popover');
            if (
                popoverEl &&
                !popoverEl.contains(event.target) &&
                !triggerEl.contains(event.target)
            )
            {
                clearInterval(timerInterval);

                popover.hide();
                document.removeEventListener('click', onClickOutside, true);
            }
        };

        setTimeout(() => {
            document.addEventListener('click', onClickOutside, true);
        }, 0);
    }, 10);
};