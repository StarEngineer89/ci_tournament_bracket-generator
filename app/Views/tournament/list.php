<div class="container justify-content-center mb-3">
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a tournament name or find out which tournaments a participant is competing in" onkeydown="handleKeyPress(event)">
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
    <?php if ($navActive == 'all') : ?>
    <a class="btn btn-success" href="<?php echo base_url('/tournaments/create') ?>"><i class="fa-sharp fa-solid fa-plus"></i> Create</a>
    <?php endif; ?>

    <div class="dropdown ms-2">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-list-check"></i> Bulk Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkDelete">Bulk Delete</a></li>
            <?php if ($navActive == 'archived') : ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkRestore">Bulk Restore</a></li>
            <?php else: ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkArchive">Bulk Archive</a></li>
            <?php endif; ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkReset">Bulk Reset</a></li>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkStatusUpdate">Bulk Status Update</a></li>
        </ul>
    </div>

    <a href="<?= base_url('tournaments/export?filter=' . $navActive) ?>" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
    <?php endif ?>
</div>
<div class="table-responsive">
    <table id="tournamentTable" class="table align-middle">
        <thead>
            <tr>
                <th scope="col" width="20px">
                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                </th>
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
                <th scope="col">Created Time<br />&nbsp;</th>
                <th scope="col">Actions<br />&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php $order = 1; ?>
            <?php foreach ($tournaments as $index => $tournament) : ?>
            <?php if (isset($tournament['status'])): ?>
            <tr data-id="<?= $tournament['id'] ?>">
                <td><input type="checkbox" class="item-checkbox form-check-input ms-2"></td>
                <td scope="row"><?= $order++ ?></td>
                <td data-label="name">
                    <a href="<?= base_url('tournaments/' . $tournament['id'] . '/view?mode=edit') ?>"><?= $tournament['name'] ?></a>
                </td>
                <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
                <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
                <td><?= convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id)) ?></td>
                <td>
                    <div class="btn-groups list-group">
                        <button class="btn text-start collapse-actions-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActions-<?= $index ?>" aria-expanded="false" aria-controls="collapseActions-<?= $index ?>">
                            <i class="fa-solid fa-plus"></i> View Actions
                        </button>
                        <div class="collapse" id="collapseActions-<?= $index ?>">
                            <div class="card card-body p-3">
                                <a href="javascript:;" class="rename" data-id="<?= $tournament['id'] ?>" onclick="renameTorunament(this)">Rename</a>
                                <a href="javascript:;" class="reset" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#resetConfirm">Reset</a>
                                <?php if ($tournament['archive'] == 1): ?>
                                <a href="javascript:;" class="restore" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#restoreConfirmModal">Restore</a>
                                <?php else: ?>
                                <a href="javascript:;" class="archive" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#archiveConfirmModal">Archive</a>
                                <?php endif ?>
                                <a href="javascript:;" class="delete" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#deleteConfirm">Delete</a>
                                <a href="javascript:;" class="change-status" data-id="<?= $tournament['id'] ?>" data-status="<?= $tournament['status'] ?>">Change Status</a>
                                <a href="javascript:;" class="music-setting-link" data-id="<?= $tournament['id'] ?>">Settings</a>
                                <a href="javascript:;" class="share" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#shareModal">Share</a>
                                <a href="javascript:;" class="view-log" data-id="<?= $tournament['id'] ?>" data-name="<?= $tournament['name'] ?>" data-bs-toggle="modal" data-bs-target="#viewLogModal">View Log</a>
                            </div>
                        </div>
                    </div>
                    <a href="javascript:;" class="save visually-hidden" data-id="<?= $tournament['id'] ?>" data-status="<?= $tournament['status'] ?>" onClick="saveChange(event)">Save</a>
                    <a href="javascript:;" class="save visually-hidden" data-id="<?= $tournament['id'] ?>" data-status="<?= $tournament['status'] ?>" onClick="cancelUpdateTorunament(this)">Cancel</a>
                </td>
            </tr>
            <?php endif ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="archiveConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="archiveConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to archive this tournament "<span class="tournament-name"></span>"?</h1>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="archiveConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="restoreConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to restore this tournament "<span class="tournament-name"></span>"?</h1>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="restoreConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>