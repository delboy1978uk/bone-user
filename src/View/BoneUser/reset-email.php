<section class="intro">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8 text-center">
                <img src="<?= $logo ?>"/>
                <br>&nbsp;
                <h1><?= $this->t('changeemail.h1', 'user') ?></h1>
                <?= null !== $message ? $this->alert($message) : '' ?>
                <p class="lead"><?= $this->t('changeemail.success', 'user') ?></p>
                <a href="/user/home" class="btn btn-success"><?= $this->t('continue', 'user') ?></a>
            </div>
        </div>
    </div>
</section>