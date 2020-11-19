<?php use Del\Icon; ?>
<section id="forgot-password">
    <div class="container">
        <div class="row  justify-content-md-center">
            <?= isset($message) ? $this->alert($message) : null; ?>
            <div class="col-md-8 text-center">
                <?= Icon::custom(Icon::ENVELOPE, 'fa-5x'); ?>
                <br>&nbsp;
                <h1><?= $this->t('resetpass.h1', 'user') ?></h1>
                <p class="lead pt10"><?= $this->t('resetpass.lead', 'user') ?></p>
                <p><?= $this->t('resetpass.p', 'user') ?></p>
            </div>
        </div>
    </div>
</section>