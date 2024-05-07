<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="/js/participants.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="container d-flex justify-content-center p-5">
        <div class="card col-12 col-md-5 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?//= lang('Auth.login') ?>Tournament Participants</h5>
                <div class="buttons">
                    <button id="add-participant" class="btn btn-primary">Add Participant</button>
                    <button id="button" class="btn btn-primary">Single Elimination</button>
                    <button id="button-double" class="btn btn-primary">Double Elimination</button>
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

                <div id="newList" class="list-group"></div>
            </div>
        </div>
    </div>

    <audio id="myAudio" style="display:none">
        <source src="<?php echo base_url('UEFA Champions League Anthem.mp3') ?>" type="audio/mpeg">
    </audio>

<?= $this->endSection() ?>
