<div class="container">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?= \Del\Icon::HOME ?>&nbsp;&nbsp;<?= $this->t('login.h1', 'user') ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">User sign in</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <?= isset($message) ? $this->alert($message) : null ?>
    <div class="row justify-content-md-center">
        <div class="login- col-md-8">
            <div class="card text-center">
                <div class="card-body login-card-body">
                    <div class="login-logo">
                        <img alt="Logo" src="<?= $logo ?>"/>
                    </div>
                    <br>
                    <?= $form->render(); ?>
                    <?php if (isset($email)) { ?>
                        <a class="pull-left"
                           href="/user/forgot-password/<?= $this->e($email); ?>"><?= $this->t('login.a', 'user') ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
