<section class="intro">
    <div class="va-top">
        <div class="container pt10">
            <div class="row">
                <?= null !== $message ? $this->alert($message) : '' ?>
            </div>
        </div>
        <div class="container">
            <div class="row "
            <div class="col-md-6 col-md-offset-3">
                <img alt="Logo" src="/img/skull_and_crossbones.png"/>
                <?php if (!$message) { ?>
                <h1><?= $this->t('activate.h1', 'user') ?></h1>
                <p class="lead"><?= $this->t('activate.p', 'user') ?></p>
                <a href="/user/home" class="btn btn-primary"><?= $this->t('activate.start', 'user') ?></a>
                <?php } else { ?>
                    <h1><?= $this->t('oops', 'user') ?></h1>
                    <p class="lead"><?= $this->t('problem', 'user') ?></p>
                <?php } if (isset($resendLink)) { ?>
                    <a class="btn btn-warning" href="<?= $resendLink ;?>"><?= $this->t('activate.resend', 'user') ?></a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
