<?php use Del\Icon; ?>
<section class="intro">
    <div class="mt20">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3 mt20">
                    <img src="/img/skull_and_crossbones.png" />
                    <?= null !== $message ? $box->alert($message) : '' ?>
                    <div class="overflow mt20">
                        <p class="lead"><?= Icon::ENVELOPE; ?> Thanks for (re)registering</p>
                        <p>An activation email has been sent to your email inbox. Please click on the link to activate
                            your account and log in to the system.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

