<script type="text/javascript" src="/bone-user/js/jquery.pstrength-min.1.2.js"></script>
<script type="text/javascript" src="/bone-user/js/register.js"></script>
<link rel="stylesheet" href="/bone-user/css/password-strength.css" />
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="<?= $logo ?>" />
                    <h1><?= $this->t('changepass.h1', 'user') ?></h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <?php
                            if ($success) {?>
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
            </div>
        </div>
    </div>
</section>