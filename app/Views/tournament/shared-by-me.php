<div class="container justify-content-center mb-3">
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a specific tournament name or find out which tournaments a participant is competing in" onkeydown="handleKeyPress(event)">
        <button class="btn btn-primary" onclick="fetchDataAndUpdateTable()"><i class="fa fa-search"></i> Search</button>
    </div>
</div>

<div class="buttons d-flex justify-content-end">
    <?php if ($navActive == 'shared'): ?>
    <div class="buttons d-flex justify-content-end mb-3">
        <input type="radio" class="btn-check" name="share-type" id="shared-by" value="by" autocomplete="off" <?= ($shareType != 'wh') ? 'checked' : '' ?>>
        <label class="btn" for="shared-by">Shared by me</label>

        <input type="radio" class="btn-check" name="share-type" id="shared-with" value="wh" autocomplete="off" <?= ($shareType == 'wh') ? 'checked' : '' ?>>
        <label class="btn" for="shared-with">Shared with me</label>
    </div>
    <?php else: ?>
    <a class="btn btn-success" href="<?php echo base_url('/tournaments/create') ?>"><i class="fa-sharp fa-solid fa-plus"></i> Create</a>
    <?php endif ?>
</div>

<table id="tournamentTable" class="shared-by-me table align-middle">
    <thead>
        <tr>
            <th scope="col" width="20px">
                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
            </th>
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
            <th scope="col">Created Time</th>
            <th scope="col">Actions</th>
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
                <a href="<?= base_url('tournaments/' . $tournament['tournament_id'] . '/view') ?>"><?= $tournament['name'] ?></a>
            </td>
            <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
            <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
            <td><?= $tournament['created_at'] ?></td>
            <td>
                <div class="btn-groups list-group">
                    <button class="btn text-start collapse-actions-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActions-<?= $index ?>" aria-expanded="false" aria-controls="collapseActions-<?= $index ?>">
                        <i class="fa-solid fa-plus"></i> View Actions
                    </button>
                    <div class="collapse" id="collapseActions-<?= $index ?>">
                        <div class="card card-body p-3">
                            <a href="javascript:;" class="rename" data-id="<?= $tournament['tournament_id'] ?>" onclick="renameTorunament(this)">Rename</a>
                            <a href="javascript:;" class="reset" data-id="<?= $tournament['tournament_id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#resetConfirm">Reset</a>
                            <a href="javascript:;" class="delete" data-id="<?= $tournament['tournament_id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#deleteConfirm">Delete</a>
                            <a href="javascript:;" class="change-status" data-id="<?= $tournament['tournament_id'] ?>" data-status="<?= $tournament['status'] ?>">Change Status</a>
                            <a href="javascript:;" class="music-setting-link" data-id="<?= $tournament['tournament_id'] ?>">Music Settings</a>
                            <a href="javascript:;" class="share" data-id="<?= $tournament['tournament_id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#shareModal">Share</a>
                            <a href="javascript:;" class="view-log" data-id="<?= $tournament['tournament_id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#viewLogModal">View Log</a>
                        </div>
                    </div>
                </div>

                <a href="javascript:;" class="save visually-hidden" data-id="<?= $tournament['tournament_id'] ?>" data-status="<?= $tournament['status'] ?>" onClick="saveChange(event)">Save</a>
                <a href="javascript:;" class="save visually-hidden" data-id="<?= $tournament['tournament_id'] ?>" data-status="<?= $tournament['status'] ?>" onClick="cancelRenameTorunament(this)">Cancel</a>
            </td>
        </tr>
        <?php endif ?>
        <?php endforeach; ?>
    </tbody>
</table>