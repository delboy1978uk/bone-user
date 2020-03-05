<section class="intro">
    <div class="intro-body">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img alt="Logo" src="/img/skull_and_crossbones.png" />
                </div>
                <div class="col-md-6">
                    <h1><?= $this->t('user.welcome', 'user') ?></h1>
                    <a href="<?= $this->l() ?>/user/login" class="btn btn-success"><?= \Del\Icon::FORWARD; ?> <?= $this->t('user.login', 'user') ?></a>
                    <a href="<?= $this->l() ?>/user/register" class="btn btn-primary"><?= \Del\Icon::EDIT; ?> <?= $this->t('user.register', 'user') ?></a>
                </div>
            </div>
            <div class="page-scroll">
                <a href="<?= $this->l() ?>/" class="btn btn-circle tt" title="Back To Bone MVC API">
                    <?= \Del\Icon::CARET_LEFT; ?>
                </a>&nbsp;
            </div>
        </div>
    </div>
</section>
