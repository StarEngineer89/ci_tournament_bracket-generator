<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Gallery<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script type="text/javascript">
var table = null;
var datatableRows;

table = $('#tournamentGalleryTable').DataTable({
    "order": [
        [0, "asc"]
    ], // Initial sorting by the first column ascending
    "paging": true, // Enable pagination
    "searching": true, // Enable search box
    scrollX: true,
    "columnDefs": [{
        "orderable": false,
        "targets": [2, 3, 7, 8]
    }],
});

$('#typeFilter').on('change', function() {
    var selectedType = $(this).val().toLowerCase();
    table.columns(2).search(selectedType).draw();
});

$('#stautsFilter').on('change', function() {
    var selectedStatus = $(this).val().toLowerCase();
    table.columns(3).search(selectedStatus).draw();
});

$('#userByFilter').on('change', function() {
    var selectedUser = $(this).val().toLowerCase().trim();
    table.columns(8).search(selectedUser).draw();
});

$(document).on('click', '.btnCopy', function(e) {
    console.log(e);
    var copyId = $(this).data("copyid");
    copyClipboard(copyId);
});

function copyClipboard(url_id) {
    // Get the text field
    var copyText = document.getElementById(url_id);

    // Select the text field
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    if (navigator.clipboard) {
        navigator.clipboard.writeText(copyText.value);
    } else {
        document.execCommand('copy');
    }
}

var nameColumns = $('td[data-label="name"] span', datatableRows)
var names = []
nameColumns.each((i, element) => {
    if (!names.includes(element.textContent.trim())) {
        var option = $(`<option value="${element.textContent.trim()}">${element.textContent}</option>`)
        $('#userByFilter').append(option)

        names.push(element.textContent.trim())
    }
})

function handleKeyPress(event) {
    if (event.keyCode === 13) {
        event.preventDefault(); // Prevent form submission
        fetchDataAndUpdateTable();
    }
}

function fetchDataAndUpdateTable() {
    let data = {
        query: $('#tournamentSearchInputBox').val()
    }

    let url = new URL(window.location.href);

    // Get search params from URL
    let searchParams = new URLSearchParams(url.search);

    // Add new parameter
    searchParams.set('query', $('#tournamentSearchInputBox').val());

    // Update search property of URL object
    url.search = searchParams.toString();

    // Replace current history state with new URL
    history.replaceState(null, '', url.href);

    window.location.href = url.href
}
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card shadow-sm">
    <div class="card- p-3">
        <div class="text-center">
            <h3>Welcome to the Tournament Gallery!</h3>
            <div class="gallery-description d-flex  flex-column justify-content-center">
                <p>Here, you can dive into the excitement of live tournaments. Whether you're signed in or just visiting, explore and spectate the action in real-time.</p>
                <p>Ready to watch some thrilling matches? Step right in, enjoy watching the competition unfold, and cheer on your favorite participants!</p>
            </div>
        </div>
        <div class="container justify-content-center mb-3">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a tournament name or find out which tournaments a participant is competing in" onkeydown="handleKeyPress(event)">
                <button class="btn btn-primary" onclick="fetchDataAndUpdateTable()"><i class="fa fa-search"></i> Search</button>
            </div>
        </div>
        <div class="buttons d-flex justify-content-end">
            <a href="<?= base_url('tournaments/create') ?>" class="btn btn-success ms-2"><i class="fa-sharp fa-solid fa-plus"></i> Create</a>
            <a href="<?= base_url('gallery/export?filter=all') ?>" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
        </div>
        <div class="table-responsive">
            <table id="tournamentGalleryTable" class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">#<br />&nbsp;</th>
                        <th scope="col">Tournament Name<br />&nbsp;</th>
                        <th scope="col">
                            <label for="typeFilter">Type:</label>
                            <select id="typeFilter" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="Single">Single</option>
                                <option value="Double">Double</option>
                            </select>
                        </th>
                        <th scope="col">
                            <label for="statusFilter">Status:</label>
                            <select id="stautsFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="In progress">In progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Abandoned">Abandoned</option>
                            </select>
                        </th>
                        <th scope="col"># Participants<br />&nbsp;</th>
                        <th scope="col">Availability Start<br />&nbsp;</th>
                        <th scope="col">Availability End<br />&nbsp;</th>
                        <th scope="col">Public URL<br />&nbsp;</th>
                        <th scope="col">
                            <label for="userByFilter">Created By:</label>
                            <select id="userByFilter" class="form-select form-select-sm">
                                <option value="">All Users</option>
                            </select>
                        </th>
                        <th scope="col">Created Time<br />&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $order = 1; ?>
                    <?php foreach ($tournaments as $index => $tournament) : ?>
                    <?php if (isset($tournament['status'])): ?>
                    <tr data-id="<?= $tournament['id'] ?>">
                        <td scope="row"><?= $order++ ?></td>
                        <td data-label="name">
                            <a href="<?= base_url('gallery/' . $tournament['id'] . '/view') ?>"><?= $tournament['name'] ?></a>
                        </td>
                        <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
                        <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
                        <td><?= $tournament['participants'] ?></td>
                        <td><?= (auth()->user() && $tournament['available_start']) ? convert_to_user_timezone($tournament['available_start'], user_timezone(auth()->user()->id)) : $tournament['available_start'] ?></td>
                        <td><?= (auth()->user() && $tournament['available_end']) ? convert_to_user_timezone($tournament['available_end'], user_timezone(auth()->user()->id)) : $tournament['available_end'] ?></td>
                        <td><?php if($tournament['public_url'] != '') :?>
                            <div class="col-auto input-group">
                                <input type="text" class="form-control" id="tournamentURL_<?= $tournament['id'] ?>" value="<?= $tournament['public_url'] ?>" aria-label="Tournament URL" aria-describedby="urlCopy" readonly="">
                                <button class="btn btn-outline-secondary input-group-text btnCopy" data-copyid="tournamentURL_<?= $tournament['id'] ?>" type="button" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="Link Copied!">Copy</button>
                            </div><?php endif;?>
                        </td>
                        <td data-label="name"><span data-toggle="tooltip" data-placement="top" title="<?= $tournament['email'] ?>"><?= $tournament['username'] ?></span></td>
                        <td><?= (auth()->user()) ? convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id)) : $tournament['created_at'] ?></td>
                    </tr>
                    <?php endif ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>