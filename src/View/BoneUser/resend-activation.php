<?php use Del\Icon; ?>
<section class="intro">
    <div class="mt20">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3 mt20">
                    <img src="<?= $logo ?>" />
                    <?= null !== $message ? $box->alert($message) : '' ?>
                    <div class="overflow mt20">
                        <p class="lead"><?= Icon::ENVELOPE; ?> <?= $this->t('resend.lead', 'user') ?></p>
                        <p><?= $this->t('resend.p', 'user') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

