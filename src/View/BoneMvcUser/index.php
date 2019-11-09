<section class="intro">
    <div class="intro-body">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img alt="Logo" src="/img/skull_and_crossbones.png" />
                </div>
                <div class="col-md-6">
                    <h1><?= $this->t('user.welcome') ?></h1>
                    <a href="/user/login" class="btn btn-success"><?= \Del\Icon::FORWARD; ?> <?= $this->t('user.login') ?></a>
                    <a href="/user/register" class="btn btn-primary"><?= \Del\Icon::EDIT; ?> <?= $this->t('user.register') ?></a>
                </div>
            </div>
            <div class="page-scroll">
                <a href="/" class="btn btn-circle tt" title="Back To Bone MVC API">
                    <?= \Del\Icon::CARET_LEFT; ?>
                </a>&nbsp;
            </div>
        </div>
    </div>
</section>