    function callShuffle () {
        const numberOfRuns = 5; // You can adjust this to the desired number of runs
        const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)
        const startTime = new Date();
        
        exampleTeams = [];
        // Use a promise to coordinate the shuffling and displaying of the message
        const shufflingPromise = new Promise(resolve => {
            const currentTime = new Date();
            function runFlipFuncSequentially(currentTime) {                
                if ((currentTime - startTime) < shuffle_duration * 1000) {
                    setTimeout(function () {
                        shuffleList(() => {
                            runFlipFuncSequentially(new Date());
                        });
                    }, delayBetweenRuns);
                } else {
                    // Resolve the promise when all shuffling iterations are complete
                    resolve();
                }
            }
            
            runFlipFuncSequentially(new Date());
        });

        shufflingPromise.then(() => {

            Array.from(itemList.children).forEach((item, i) => {
                exampleTeams.push({'id': item.id, 'name': item.textContent, 'order': i});
            });

            saveParticipantList(exampleTeams);
            },
            function(error) {myDisplayer(error);}
        );
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
        participantsArray.forEach((participant, i) => {
            var item = document.createElement('div');
            item.setAttribute('id', participant.id);
            item.setAttribute('class', "list-group-item");
            item.setAttribute('data-id', participant.id);
            item.innerHTML = participant.name;

            if (itemList.length > 0)
                itemList.insertBefore(item);
            else 
                itemList.appendChild(item);

        });

        $('#newList').contextMenu({
            selector: '.list-group-item',
            items: {
                delete: {
                    name: "Delete",
                    callback: (key, opt, e) => {
                        var element_id = opt.$trigger.data('id');
                        $.ajax({
                            type: "DELETE",
                            url: apiURL + '/participants/delete/' + element_id,
                            success: function(result) {
                                document.getElementById(element_id).remove();
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
    }

    /**
     * Initialize the list of Participants
     */
    function loadParticipants() {
        $("#overlay").fadeIn(300);

        $.ajax({
            type: "GET",
            url: apiURL + '/participants',
            dataType: "JSON",
            success: function(result) {
                renderParticipants(result);
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

function saveParticipantList(list) {
    $.ajax({
        type: "post",
        url: apiURL + '/participants/updateList/',
        data: {'list' : JSON.stringify(list)},
        // contentType: 'application/json',
        dataType: "JSON",
        success: function(result) {
            if (result.result == 'success')
                generateBrackets();
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

function generateBrackets() {
    $.ajax({
        type: "post",
        url: apiURL + '/brackets/generate',
        data: {'type': eleminationType, 'tournament_id': tournament_id},
        dataType: "JSON",
        success: function(result) {
            if (result.result == 'success') window.location.href = '/tournaments/' + tournament_id + '/view';
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

$(document).ready(function() {
    $('#tournamentSettings input[type="radio"]').on('change', function() {
        if ($(this).data('target') == 'file')
            $(this).parent().parent().children('[data-source="file"]').attr('disabled', false);
        if ($(this).data('target') == 'url')
            $(this).parent().parent().children('[data-source="url"]').attr('disabled', false);
    });

    $('.startAt, .stopAt').on('change', function() {
        const starttime = $(this).parents('.preview').find('.startAt').val();
        const stoptime = $(this).parents('.preview').find('.stopAt').val();

        if (starttime !== 'undefined' && stoptime !== 'undefined' && starttime !== '' && stoptime !== '') {
            $(this).parents('.preview').find('.duration').val(stoptime - starttime);
        }
    });

    $('.duration').on('change', function() {
        const starttime = $(this).parents('.preview').find('.startAt').val();
        const duration = $(this).parents('.preview').find('.duration').val();

        if (starttime !== 'undefined' && duration !== 'undefined' && starttime !== '' && duration !== '') {
            $(this).parents('.stopAt').find('.duration').val(parseInt(starttime) + parseInt(duration));
        }
    });

    $('.music-source[data-source="file"]').on('change', function(e) {
        e.preventDefault();

        let panel = $(this).parent();
        let index = $('.music-source[data-source="file"]').index($(this));
        $(this).parents('.music-setting').find('input[type="radio"][value="f"]').prop('checked', true);

        var formData = new FormData();
        formData.append('audio', $(this)[0].files[0]);
        $.ajax({
            url: apiURL + '/tournaments/upload',
            type: "POST",
            data:  formData,
            contentType: false,
            cache: false,
            processData:false,
            beforeSend : function()
            {
                //$("#preview").fadeOut();
                $("#err").fadeOut();
            },
            success: function(data)
            {
                var data = JSON.parse(data);
                if(data.error)
                {
                    // invalid file format.
                    $("#err").html("Invalid File !").fadeIn();
                }
                else
                {
                    panel.find('input[type="hidden"]').val(data.path);
                    $('.playerSource').eq(index).attr('src', '/uploads/' + data.path);
                    $('.player').eq(index).load();
                    $(".preview").eq(index).fadeIn();
                }
            },
            error: function(e) 
            {
                $("#err").html(e).fadeIn();
            }          
        });
    });
});


