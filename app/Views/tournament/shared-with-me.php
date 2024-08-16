<div class="container justify-content-center mb-3">
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a tournament name or find out which tournaments a participant is competing in" onkeydown="handleKeyPress(event)">
        <button class="btn btn-primary" onclick="fetchDataAndUpdateTable()"><i class="fa fa-search"></i> Search</button>
    </div>
</div>

<div class="buttons d-flex justify-content-end mb-3">
    <input type="radio" class="btn-check" name="share-type" id="shared-by" value="by" autocomplete="off" <?= ($shareType != 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-by">Shared by me</label>

    <input type="radio" class="btn-check" name="share-type" id="shared-with" value="wh" autocomplete="off" <?= ($shareType == 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-with">Shared with me</label>

    <a href="<?= base_url('tournaments/export?filter=shared&type=wh') ?>" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
</div>
<div class="table-responsive">
    <table id="tournamentTable" class="table align-middle">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Tournament Name</th>
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
                <th scope="col">
                    <label for="accessibilityFilter">Accessibility:</label>
                    <select id="accessibilityFilter" class="form-select form-select-sm">
                        <option value="">All Accessibility</option>
                        <option value="Can Edit">Can Edit</option>
                        <option value="Can View">Can View</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="userByFilter">Shared By:</label>
                    <select id="userByFilter" class="form-select form-select-sm">
                        <option value="">All Users</option>
                    </select>
                </th>
                <th scope="col">Shared Time</th>
            </tr>
        </thead>
        <tbody>
            <?php $order = 1; ?>
            <?php foreach ($tournaments as $index => $tournament) : ?>
            <?php if (isset($tournament['status'])): ?>
            <tr data-id="<?= $tournament['tournament_id'] ?>">
                <td scope="row"><?= $order++ ?></td>
                <td>
                    <a href="<?= base_url('tournaments/shared/' . $tournament['token']) ?>"><?= $tournament['name'] ?></a>
                </td>
                <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
                <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
                <td>
                    <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-title="<?= ($tournament['permission'] == SHARE_PERMISSION_EDIT) ? 'You can view and execute actions on the tournament brackets.
    Note that actions are logged for tracking purposes.' : 'You can view the tournament brackets.' ?>">
                        <button class="btn" type="button" disabled><?= ($tournament['permission'] == SHARE_PERMISSION_EDIT) ? 'Can Edit' : 'Can View' ?></button>
                    </span>
                </td>
                <td data-label="name">
                    <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-title="<?= $tournament['email'] ?>">
                        <button class="btn" type="button" disabled><?= $tournament['username'] ?></button>
                    </span>

                </td>
                <td>
                    <?= convert_to_user_timezone($tournament['access_time'], user_timezone(auth()->user()->id)) ?>
                </td>
            </tr>
            <?php endif ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>