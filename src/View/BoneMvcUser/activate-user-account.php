<section class="intro">
    <div class="va-top">
        <div class="container pt10">
            <div class="row">
                <?= null !== $message ? $box->alert($message) : '' ?>
            </div>
        </div>
        <div class="container">
            <div class="row "
            <div class="col-md-6 col-md-offset-3">
                <img alt="Logo" src="/img/skull_and_crossbones.png"/>
                <?php if (!$message) { ?>
                <h1>Welcome, user.</h1>
                <p class="lead">Your user account is activated and you have been logged in!</p>
                <a href="/user/home" class="btn btn-primary">Get started</a>
                <?php } else { ?>
                    <h1>Oops.</h1>
                    <p class="lead">There was a problem</p>
                <?php } if (isset($resendLink)) { ?>
                    <a class="btn btn-warning" href="<?= $resendLink ;?>">Resend activation link</a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
