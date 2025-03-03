<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<style>
* {
    font-family: Corbel;
}

p,
div,
input {
    font-size: 18px;
}

.ui-autocomplete {
    cursor: pointer;
    height: 300px;
    overflow-y: scroll;
}

a {
    color: blue;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/jquery-ui.min.js" integrity="sha512-MlEyuwT6VkRXExjj8CdBKNgd+e2H+aYZOCUaCrt9KRk6MlZDOs91V1yK22rwm8aCIsb5Ec1euL8f0g58RKT/Pg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script type="text/javascript">
var participantsTable = null;
var participantsTableRows;
var participantNames = [];
var tournamentList = [];

participantsTable = $('#participantLeaderboardTable').DataTable({
    "searching": true,
    "processing": true,
    "ajax": {
        "url": apiURL + '/participants/get-leaderboard' + window.location.search,
        "type": "POST",
        "dataSrc": "",
        "data": function(d) {
            d.user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>; // Include the user_id parameter
            d.participant = $('#pt-names').val();
            d.tournament = $('#tournamentFilter').val();
        }
    },
    "order": [
        [0, "asc"]
    ], // Initial sorting by the first column ascending
    "paging": true, // Enable pagination
    scrollX: true,
    "columnDefs": [{
        "orderable": false,
        "targets": [1, 4]
    }],
    // Add custom initComplete to initialize select all checkbox
    "initComplete": function(settings, json) {
        participantsTableRows = participantsTable.rows({
            'search': 'applied'
        }).nodes();

        var nameColumns = $('td[data-label="name"]', participantsTableRows)
        nameColumns.each((i, element) => {
            if (!participantNames.includes(element.textContent.trim())) {
                participantNames.push(element.textContent.trim())
            }
        })
        $('#pt-names').autocomplete({
            source: participantNames,
            minLength: 0,
            scroll: true,
            change: function(event, ui) {
                participantsTable.ajax.reload()
            },
            close: function(event, ui) {
                participantsTable.ajax.reload()
            }
        }).focus(function() {
            $(this).autocomplete("search", "");
        })

        // tournamentFilter
        $('#tournamentFilter').autocomplete({
            source: tournamentList,
            minLength: 0,
            scroll: true,
            change: function(event, ui) {
                participantsTable.ajax.reload()
            },
            close: function(event, ui) {
                participantsTable.ajax.reload()
            }
        }).focus(function() {
            $(this).autocomplete("search", "");
        })

        let requestCompleted = false;

        // Set a timeout to check if the request exceeds the time limit
        const timeout = () => {
            requestCompleted = false
            setTimeout(() => {
                if (!requestCompleted) {
                    $('#beforeProcessing').removeClass('d-none')
                    // You can also abort the request here if needed
                    // xhr.abort(); // Uncomment if you implement an XMLHttpRequest
                }
            }, 1000);
        }

        $('#participantLeaderboardTable').on('preXhr.dt', function() {
            // $('#beforeProcessing').removeClass('d-none')
            timeout();
        });

        // Hide custom loading overlay after reload
        $('#participantLeaderboardTable').on('xhr.dt', function() {
            requestCompleted = true; // Mark the request as completed
            clearTimeout(timeout); // Clear the timeout
            $('#beforeProcessing').addClass('d-none')
        });
    },
    "columns": [{
            "data": null,
            "render": function(data, type, row, meta) {
                return meta.row + 1; // Display index number
            }
        },
        {
            "data": "name",
            "createdCell": function(td, cellData, rowData, row, col) {
                $(td).attr('data-label', 'name');
            }
        },
        {
            "data": "brackets_won",
            "className": "text-center"
        },
        {
            "data": "tournaments_won",
            "className": "text-center"
        },
        {
            "data": null,
            "render": function(data, type, row, meta) {
                if (row.tournaments_list) {
                    let listHtml = ''
                    let moreHtml = ''
                    row.tournaments_list.forEach((tournament, i) => {
                        if (!tournamentList.includes(tournament.name)) {
                            tournamentList.push(tournament.name)
                        }

                        if (i > 3) {
                            moreHtml += `<a href="<?= base_url('tournaments') ?>/${tournament.id}/view">${tournament.name}</a>`
                            return
                        }

                        if (i == 3) {
                            listHtml += ' ...<br/><a href="javascript:;" onclick="readMore()">Read More</a>'
                            moreHtml += `<a href="<?= base_url('tournaments') ?>/${tournament.id}/view">${tournament.name}</a>`
                            return
                        }

                        listHtml += `<a href="<?= base_url('tournaments') ?>/${tournament.id}/view">${tournament.name}</a>`
                        if (i < row.tournaments_list.length - 1) {
                            listHtml += ', '
                        }


                    })

                    return `<span class="list">${listHtml}</span><span class="more d-none">${moreHtml}</span>`
                }

                return ``;
            }
        },
        {
            "data": 'accumulated_score',
            "className": "text-center"
        },
        {
            "data": "votes",
            "className": "text-center"
        },
    ],
    "createdRow": function(row, data, dataIndex) {
        // Add a custom attribute to the row
        $(row).attr('data-id', data.id); // Adds a data-id attribute with the row's ID
    }
});


const notePlaceholder = document.getElementById('notePlaceholder')
const appendNoteAlert = (message, type) => {
    const wrapper = document.createElement('div')
    wrapper.innerHTML = [
        `<div class="container alert alert-${type} alert-dismissible" id="noteAlert" role="alert">`,
        `   <div>${message}</div>`,
        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
        '</div>'
    ].join('')

    notePlaceholder.append(wrapper)
}

const noteAlertTrigger = document.getElementById('toggleNoteBtn')
if (noteAlertTrigger) {
    const msg = $('#noteMsg').html();
    noteAlertTrigger.addEventListener('click', () => {
        appendNoteAlert(msg, 'success')
        noteAlertTrigger.classList.add('d-none')

        const myAlert = document.getElementById('noteAlert')
        myAlert.addEventListener('closed.bs.alert', event => {
            noteAlertTrigger.classList.remove('d-none')
        })
    })
}

$('#toggleNoteBtn').click();
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card shadow-sm">
    <div class="card- p-3">
        <div class="container">
            <div class="text-center">
                <h3>Participant Leaderboard</h3>
                <p>Discover the top-performing participants across all tournaments. See who’s dominating the competition and climbing to the top with every victory!</p>
            </div>

            <div class="container alert-btn-container mb-1 d-flex justify-content-end">
                <button type="button" class="btn" id="toggleNoteBtn">
                    <i class="fa-solid fa-circle-info"></i>
                </button>
            </div>

            <div id="notePlaceholder"></div>
            <div id="noteMsg" class="d-none">

                Note:<br />
                By default, the top participants are ranked by the number of tournaments they’ve won.<br />
                Registered participants (prefixed with @) who were explicitly added/invited by a host are grouped under a single record, ensuring accurate tracking of their achievements.<br />
                In contrast, anonymous participants have separate records for each tournament they join. <br />
                Even if an anonymous participant uses the same name across multiple tournaments, there is no way to verify if they are the same individual or different participants.<br />
                This is one of the key benefits of registration—it allows for proper verification, ensuring consistency and prioritizing registered participants on the leaderboard!
            </div>
        </div>

        <div class="buttons d-flex justify-content-end">
            <a href="<?= base_url('participants/export') ?>" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
        </div>
        <div class="table-responsive">
            <table id="participantLeaderboardTable" class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">
                            <label for="pt-names">Participant Name</label>
                            <input type="text" id="pt-names" class="form-control form-control-sm" />
                        </th>
                        <th scope="col" class="text-center">Brackets Won</th>
                        <th scope="col" class="text-center">Tournaments Won</th>
                        <th scope="col">
                            <label for="tournamentFilter">Participated Tournaments</label>
                            <input type="text" id="tournamentFilter" class="form-control form-control-sm" />
                        </th>
                        <th scope="col" class="text-center">Accumulated Score</th>
                        <th scope="col" class="text-center">Votes</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>