<?php use Del\Icon; ?>
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <?= null !== $message ? $this->alert($message) : null; ?>
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png" />
                    <h1>Reset your password</h1>
                    <div class="page-scroll">
                        <div class="well overflow" style="color: black;">
                            <div class="tc">
                                <?= Icon::custom(Icon::ENVELOPE,'fa-5x') ;?>
                            </div>
                            <p class="lead pt10">If an account was found, an email has been sent to it.</p>
                            <p>Check your inbox and click on the secure link to reset your password.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>