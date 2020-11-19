<section id="login-section">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="text-center">
                    <img src="<?= $logo ?>"/>
                    <h1 class="mt10 "><?= $this->t('login.h1', 'user') ?></h1>
                    <?= isset($message) ? $this->alert($message) : null ?>
                </div>
                <div class="well" style="color: black;">
                    <?= $form->render(); ?>
                    <?php if (isset($email)) { ?>
                        <a class="pull-left"
                           href="/website/forgot-password/<?= $this->e($email); ?>"><?= $this->t('login.a', 'user') ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>