<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script type="text/javascript">
var participantsTable = null;
var participantsTableRows;

participantsTable = $('#participantLeaderboardTable').DataTable({
    "searching": true,
    "processing": true,
    "ajax": {
        "url": apiURL + '/participants/get-leaderboard' + window.location.search,
        "type": "POST",
        "dataSrc": "",
        "data": function(d) {
            d.user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>; // Include the user_id parameter
            d.search_participant = $('#tournamentSearchInputBox').val();
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
        var names = []
        nameColumns.each((i, element) => {
            if (!names.includes(element.textContent.trim())) {
                var option = $(`<option value="${element.textContent.trim()}">${element.textContent}</option>`)
                $('#participantNameFilter').append(option)

                names.push(element.textContent.trim())
            }
        })

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
                    })

                    return listHtml
                }

                return ``; // Display index number
            }
        },
        {
            "data": "top_score",
            "className": "text-center"
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

$('#participantNameFilter').on('change', function() {
    var selectedUser = $(this).val().toLowerCase().trim();
    participantsTable.columns(1).search(selectedUser).draw();
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
                <p>Note: By default, the top 100 participants are ranked by the number of tournaments they’ve won.</p>
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
                            <label for="participantNameFilter">Participant Name</label>
                            <select id="participantNameFilter" class="form-select form-select-sm">
                                <option value="">All Users</option>
                            </select>
                        </th>
                        <th scope="col" class="text-center">Brackets Won</th>
                        <th scope="col" class="text-center">Tournaments Won</th>
                        <th scope="col">Tournaments List</th>
                        <th scope="col" class="text-center">Top Score</th>
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