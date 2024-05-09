<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Dashboard<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="card col-12 shadow-sm">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-center"><?//= lang('Auth.login') ?>Tournament Dashboard</h5>
            <div class="buttons d-flex justify-content-end">
            <a class="btn btn-primary" href="<?php echo base_url('/tournaments/create') ?>">Create</a>
            </div>

            <?php if (session('error') !== null) : ?>
                <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
            <?php elseif (session('errors') !== null) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php if (is_array(session('errors'))) : ?>
                        <?php foreach (session('errors') as $error) : ?>
                            <?= $error ?>
                            <br>
                        <?php endforeach ?>
                    <?php else : ?>
                        <?= session('errors') ?>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php if (session('message') !== null) : ?>
            <div class="alert alert-success" role="alert"><?= session('message') ?></div>
            <?php endif ?>

            <table class="table align-middle">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">Tournament Name</th>
                <th scope="col">Type</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <th scope="row">1</th>
                <td>
                    <a href="<?= base_url('tournaments/1/view') ?>">Tournament 1</a>
                </td>
                <td>Single</td>
                <td>In progress</td>
                <td>
                    <div class="list-group">
                        <a href="">Rename</a>
                        <a href="">Reset</a>
                        <a href="">Music Settings</a>
                    </div>
                </td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>
