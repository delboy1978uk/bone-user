<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="<?= $logo ?>" />
                    <h1><?= $this->t('changeemail.h1', 'user') ?></h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <p class="lead"><?= $this->t('changeemail.success', 'user') ?></p>
                            <a href="/user/home" class="btn btn-success"><?= $this->t('continue', 'user') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>