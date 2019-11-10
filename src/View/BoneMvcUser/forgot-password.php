<?php use Del\Icon; ?>
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <?= null !== $message ? $this->alert($message) : null; ?>
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png" />
                    <h1><?= $this->t('resetpass.h1', 'user') ?></h1>
                    <div class="page-scroll">
                        <div class="well overflow" style="color: black;">
                            <div class="tc">
                                <?= Icon::custom(Icon::ENVELOPE,'fa-5x') ;?>
                            </div>
                            <p class="lead pt10"><?= $this->t('resetpass.lead', 'user') ?></p>
                            <p><?= $this->t('resetpass.p', 'user') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>