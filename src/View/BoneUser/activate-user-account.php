<section id="activate-account">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <div class="text-center">
                    <img alt="Logo" src="<?= $logo ?>"/>
                    <br>&nbsp;
                </div>
                <?php if (!$message) { ?>
                    <h1><?= $this->t('activate.h1', 'user') ?></h1>
                    <p class="lead"><?= $this->t('activate.p', 'user') ?></p>
                    <a href="/user/home" class="btn btn-primary"><?= $this->t('activate.start', 'user') ?></a>
                <?php } else { ?>
                    <?= null !== $message ? '<br />' . $this->alert($message) : '' ?>
                    <h1><?= $this->t('oops', 'user') ?></h1>
                    <p class="lead"><?= $this->t('problem', 'user') ?></p>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
