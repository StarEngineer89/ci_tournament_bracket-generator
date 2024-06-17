<div class="buttons d-flex justify-content-end mb-3">
    <input type="radio" class="btn-check" name="share-type" id="shared-by" autocomplete="off" checked>
    <label class="btn" for="shared-by">Shared by me</label>

    <input type="radio" class="btn-check" name="share-type" id="shared-with" autocomplete="off">
    <label class="btn" for="shared-with">Shared with me</label>
</div>

<table class="table align-middle">
    <thead>
        <tr>
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
        <tr data-id="<?= $tournament['id'] ?>">
            <th scope="row"><?= $order++ ?></th>
            <td>
                <a href="<?= base_url('tournaments/shared/' . $tournament['token']) ?>"><?= $tournament['name'] ?></a>
            </td>
            <td><?= ($tournament['type'] == 1) ? "Single" : "Double" ?></td>
            <td data-label="status"><?= TOURNAMENT_STATUS_LABELS[$tournament['status']] ?></td>
            <td><?php log_message('debug', json_encode($tournament)); ?>
                <span class="d-inline-block" tabindex="<?= $order++ ?></th>" data-bs-toggle="tooltip" data-bs-title="<?= ($tournament['permission'] == SHARE_PERMISSION_EDIT) ? 'You can view and execute actions on the tournament brackets.
Note that actions are logged for tracking purposes.' : 'You can view the tournament brackets.' ?>">
                    <button class="btn" type="button" disabled><?= ($tournament['permission'] == SHARE_PERMISSION_EDIT) ? 'Can Edit' : 'Can View' ?></button>
                </span>
            </td>
            <td>
                <span class="d-inline-block" tabindex="<?= $order++ ?></th>" data-bs-toggle="tooltip" data-bs-title="<?= $tournament['email'] ?>">
                    <button class="btn" type="button" disabled><?= $tournament['username'] ?></button>
                </span>

            </td>
            <td>
                <?= $tournament['created_at'] ?>
            </td>
        </tr>
        <?php endif ?>
        <?php endforeach; ?>
    </tbody>
</table>