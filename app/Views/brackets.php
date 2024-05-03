<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="/js/brackets.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="container d-flex justify-content-center p-5">
        <div class="card col-12 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-5"><?//= lang('Auth.login') ?>Tournament Brackets</h5>
                <div class="btns">
                    <button id="reset-single" class="btn btn-default">Reset (Single)</button>
                    <button id="reset-double" class="btn btn-default">Reset (Double)</button>
                    <button id="clear" class="btn btn-default">Reset (Clear)</button>
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

                <div id="brackets" class="brackets"></div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
