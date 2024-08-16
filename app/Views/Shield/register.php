<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.register') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="header p-2 border-bottom sticky-top bg-light">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-md navbar-light bg-body-tertiary">
                <a class="navbar-brand me-5" href="<?= base_url() ?>">Logo</a>

                <button class="navbar-toggler order-sm-5" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?= base_url() ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('gallery') ?>">Tournament Gallery</a>
                        </li>
                    </ul>
                </div>

            </nav>
        </div>
    </div>
    <div class="container d-flex justify-content-center p-5">
        <div class="card col-12 col-md-5 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-5"><?= lang('Auth.register') ?></h5>

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

                <form action="<?= url_to('register') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="form-floating mb-2">
                        <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>" required>
                        <label for="floatingEmailInput"><?= lang('Auth.email') ?></label>
                    </div>

                    <!-- Username -->
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text" autocomplete="username" placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>" required>
                        <label for="floatingUsernameInput"><?= lang('Auth.username') ?></label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-2">
                        <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.password') ?>" required>
                        <label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
                    </div>

                    <!-- Password (Again) -->
                    <div class="form-floating mb-5">
                        <input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.passwordConfirm') ?>" required>
                        <label for="floatingPasswordConfirmInput"><?= lang('Auth.passwordConfirm') ?></label>
                    </div>

                    <div class="d-grid col-12 col-md-8 mx-auto m-3">
                        <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.register') ?></button>
                    </div>

                    <p class="text-center"><?= lang('Auth.haveAccount') ?> <a href="<?= url_to('login') ?>"><?= lang('Auth.login') ?></a></p>

                </form>
            </div>
        </div>
    </div>
    <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
<?= $this->endSection() ?>
