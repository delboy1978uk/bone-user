<section class="intro">
    <div class="pt50">
        <div class="container">
            <?= null !== $message ? $this->alert($message) : null ?>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <img alt="Logo" src="<?= $logo ?>" />
                    <h1><?= $this->t('home.welcome', 'user') . $user->getEmail() ?></h1>
                    <p class="lead"><?= $this->t('home.placeholder', 'user') ?></p>
                    <a class="btn btn-success" href="/user/edit-profile"><?= $this->t('home.editprofile', 'user') ?></a>
                    <a class="btn btn-primary" href="/user/change-email"><?= $this->t('home.changeemail', 'user') ?></a>
                    <a class="btn btn-warning" href="/user/change-password"><?= $this->t('home.changepass', 'user') ?></a>
                    <a class="btn btn-danger" href="/user/logout"><?= $this->t('home.logout', 'user') ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
