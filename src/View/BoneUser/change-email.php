<?php use Del\Icon; ?>
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="<?= $logo ?>" />
                    <h1><?= $this->t('changeemail.h1', 'user') ?></h1>
                    <?= isset($message) ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <div class="tc">
                                <?= Icon::custom(Icon::ENVELOPE,'fa-5x') ;?>
                            </div>
                            <?php if (isset($form)) { ?>
                                <p class="lead"><?= $this->t('changeemail.p', 'user') ?></p>
                                <?= $form->render();
                            } else { ?>
                                <p class="lead"><?= $this->t('changeemail.check', 'user') ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>