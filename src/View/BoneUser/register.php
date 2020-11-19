<script type="text/javascript" src="/bone-user/js/jquery.pstrength-min.1.2.js"></script>
<script type="text/javascript" src="/bone-user/js/register.js"></script>
<link rel="stylesheet" href="/bone-user/css/password-strength.css"/>

<section id="register">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="text-center">
                    <img class="mb-4" src="<?= $logo ?>"/>
                    <h1> <?= $this->t('user.register', 'user'); ?></h1>
                </div>
                <?= null !== $message ? $this->alert($message) : '' ?>
                <br>
                <div class="page-scroll">
                    <div class="well" style="color: black;">
                        <?= $form->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>