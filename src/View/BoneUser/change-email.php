<?php use Del\Icon; ?>
<section class="intro">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8 text-center">
                <?= Icon::custom(Icon::ENVELOPE, 'fa-5x'); ?>
                <br>&nbsp;
                <h1><?= $this->t('changeemail.h1', 'user') ?></h1>
                <?= isset($message) ? $this->alert($message) : '' ?>
                <?php if (isset($form)) { ?>
                    <p class="lead"><?= $this->t('changeemail.p', 'user') ?></p>
                    <?= $form->render();
                } else { ?>
                    <p class="lead"><?= $this->t('changeemail.check', 'user') ?></p>
                <?php } ?>
            </div>
        </div>
    </div>

</section>