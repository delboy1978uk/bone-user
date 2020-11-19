<?php use Del\Icon; ?>
<section id="resend-activation">
    <div class="mt20">
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-md-6 mt20">
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="overflow mt20">
                        <div class="text-center">
                            <?= Icon::custom(Icon::ENVELOPE, 'fa-5x'); ?>
                            <br>&nbsp;
                        </div>
                        <h1 class="text-center"><?= $this->t('resend.lead', 'user') ?></h1>
                        <p class="lead"><?= $this->t('resend.p', 'user') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

