<div class="container justify-content-center mb-3">
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a specific tournament name or find out which tournaments a participant is competing in" onkeydown="handleKeyPress(event)">
        <button class="btn btn-primary" onclick="fetchDataAndUpdateTable()"><i class="fa fa-search"></i> Search</button>
    </div>
</div>

<div class="buttons d-flex justify-content-end mb-3">
    <input type="radio" class="btn-check" name="share-type" id="shared-by" value="by" autocomplete="off" <?= ($shareType != 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-by">Shared by me</label>

    <input type="radio" class="btn-check" name="share-type" id="shared-with" value="wh" autocomplete="off" <?= ($shareType == 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-with">Shared with me</label>
</div>

<table id="tournamentTable" class="table align-middle">
    <thead>
        <tr>
            <th scope="col" width="20px">
                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
            </th>
            <th scope="col">#</th>
            <th scope="col">Tournament Name</th>
            <th scope="col">Type</th>
            <th scope="col">Status</th>
            <th scope="col">Accessibility</th>
            <th scope="col">Shared By</th>
            <th scope="col">Shared Time</th>
        </tr>
    </thead>
    <tbody>
        <?php $order = 1; ?>
        <?php foreach ($tournaments as $index => $tournament) : ?>
        <?php if (isset($tournament['status'])): ?>
        <tr data-id="<?= $tournament['tournament_id'] ?>">
            <td><input type="checkbox" class="item-checkbox form-check-input ms-2"></td>
            <td scope="row"><?= $order++ ?></td>
            <td data-label="name">
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
            <td>
                <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-title="<?= $tournament['email'] ?>">
                    <button class="btn" type="button" disabled><?= $tournament['username'] ?></button>
                </span>

            </td>
            <td>
                <?= $tournament['access_time'] ?>
            </td>
        </tr>
        <?php endif ?>
        <?php endforeach; ?>
    </tbody>
</table>