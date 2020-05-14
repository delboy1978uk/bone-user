<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <?= isset($message) ? $this->alert($message): null ?>
                <div class="col-md-8 col-md-offset-2">

                    <h1><img src="<?= $logo ?>" /> <?= $this->t('login.h1', 'user') ?></h1>
                    <div class="page-scroll">
                        <div class="well overflow" style="color: black;">
                            <?= $form->render(); ?>
                            <?php if(isset($email)) { ?>
                            <a class="pull-left" href="/website/forgot-password/<?= $this->e($email) ;?>"><?= $this->t('login.a', 'user') ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>