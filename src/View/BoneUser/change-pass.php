<script type="text/javascript" src="/bone-user/js/jquery.pstrength-min.1.2.js"></script>
<script type="text/javascript" src="/bone-user/js/register.js"></script>
<link rel="stylesheet" href="/bone-user/css/password-strength.css"/>

<section id="change-password">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8 text-center">
                <img src="<?= $logo ?>"/>
                <br>&nbsp;
                <h1><?= $this->t('changepass.h1', 'user') ?></h1>
                <?= null !== $message ? $this->alert($message) : '' ?>
                <?php
                if ($success) { ?>
                    <p class="lead"><?= $this->t('changepass.p', 'user') ?></p>
                    <a class="btn btn-success" href="/user/home"><?= $this->t('changepass.continue', 'user') ?></a>
                <?php } else { ?>
                    <p class="lead"><?= $this->t('changepass.choose', 'user') ?></p>
                    <?= $form->render();
                }
                ?>
            </div>
        </div>
    </div>
</section>