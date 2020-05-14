<script type="text/javascript" src="/bone-user/js/jquery.pstrength-min.1.2.js"></script>
<script type="text/javascript" src="/bone-user/js/register.js"></script>
<link rel="stylesheet" href="/bone-user/css/password-strength.css" />
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1><img src="<?= $logo ?>" /> <?= $this->t('user.register', 'user') ;?></h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <?= $form->render(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>