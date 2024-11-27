<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/themes/base/jquery-ui.min.css" integrity="sha512-F8mgNaoH6SSws+tuDTveIu+hx6JkVcuLqTQ/S/KJaHJjGc8eUxIrBawMnasq2FDlfo7FYsD8buQXVwD+0upbcA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            setTimeout(() => {
                if (!requestCompleted) {
                    console.warn("The request took too long!");
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
                    row.tournaments_list.forEach((tournament, i) => {
                        listHtml += `<a href="<?= base_url('tournaments') ?>/${tournament.id}/view">${tournament.name}</a>`
                        if (i < row.tournaments_list.length - 1) {
                            listHtml += ', '
                        }

                        if (!tournamentList.includes(tournament.name)) {
                            tournamentList.push(tournament.name)
                        }
                    })

                    return listHtml
                }

                return ``; // Display index number
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
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card shadow-sm">
    <div class="card- p-3">
        <div class="text-center mb-5">
            <h3>Participant Leaderboard</h3>
            <div class="d-flex  flex-column justify-content-center">
                <p>Discover the top-performing participants across all tournaments. See who’s dominating the competition and climbing to the top with every victory!</p>
                <p>Note: By default, the top participants are ranked by the number of tournaments they’ve won.</p>
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