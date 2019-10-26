<?php use Del\Icon; ?>
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png" />
                    <h1>Change your email</h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <div class="tc">
                                <?= Icon::custom(Icon::ENVELOPE,'fa-5x') ;?>
                            </div>
                            <?php if (isset($form)) { ?>
                                <p class="lead">
                                    A confirmation email will be sent to your old email address.
                                </p>
                                <?= $form->render();
                            } else { ?>
                                <p class="lead">Check your email</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>