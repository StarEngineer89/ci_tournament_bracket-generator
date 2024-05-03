let apiURL = "http://localhost:8080/api";

$(document).on('ready', function() {

    const itemList = document.getElementById('newList');
    
    let audio = document.getElementById("myAudio");
    
    loadParticipants();
    // loadBrackets();
    
    function callShuffle () {
        const numberOfRuns = 5; // You can adjust this to the desired number of runs
        const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)
        
        exampleTeams = [];
        // Use a promise to coordinate the shuffling and displaying of the message
        const shufflingPromise = new Promise(resolve => {
            function runFlipFuncSequentially(count) {
                if (count < numberOfRuns) {
                    setTimeout(function () {
                        shuffleList(() => {
                            runFlipFuncSequentially(count + 1);
                        });
                    }, delayBetweenRuns);
                } else {
                    // Resolve the promise when all shuffling iterations are complete
                    resolve();
                }
            }
    
            runFlipFuncSequentially(0);
        });

        shufflingPromise.then(() => {

            Array.from(itemList.children).forEach((item, i) => {
                exampleTeams.push({'id': item.id, 'name': item.textContent, 'order': i});
            });

            saveParticipantList(exampleTeams);
            
            audio.pause();
        },
        function(error) {myDisplayer(error);}
    );
    }
    
    function shuffleList(callback) {
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

    $('#button').on('click', function() {
        eleminationType = "Single";
        // audio.play();
        callShuffle();
    });

    $('#button-double').on('click', function() {
        eleminationType = "Double";
        audio.play();
        callShuffle();
    });

    $('#add-participant').on('click', function() {
        var opts = prompt('Participant Name:', 'Guild');
        
        if(!_.isNaN(opts)) {
            $("#overlay").fadeIn(300);

            $.ajax({
                type: "POST",
                url: apiURL + '/participants/new',
                data: { 'name': opts },
                dataType: "JSON",
                success: function(result) {
                    var participants = result.participant;
                    renderParticipants(participants);
                },
                error: function(error) {
                    console.log(error);
                }
            }).done(() => {
                setTimeout(function(){
                    $("#overlay").fadeOut(300);
                },500);
            });
        } else
            alert('Please input the name of the participant.');
    });
});

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

