<section id="user-welcome">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-6 text-center">
                <img alt="Logo" src="<?= $logo ?>"/>
                <br>&nbsp;
                <h1><?= $this->t('user.welcome', 'user') ?></h1>
                <a href="<?= $this->l() ?>/user/login"
                   class="btn btn-success"><?= \Del\Icon::FORWARD; ?> <?= $this->t('user.login', 'user') ?></a>
                <a href="<?= $this->l() ?>/user/register"
                   class="btn btn-primary"><?= \Del\Icon::EDIT; ?> <?= $this->t('user.register', 'user') ?></a>
            </div>
        </div>
        <a href="<?= $this->l() ?>/" class="btn btn-circle tt" title="Back To Bone MVC API">
            <?= \Del\Icon::CARET_LEFT; ?>
        </a>&nbsp;
    </div>
</section>
