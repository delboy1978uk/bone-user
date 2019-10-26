<script type="text/javascript" src="/bone-mvc-user/js/jquery.pstrength-min.1.2.js"></script>
<script type="text/javascript" src="/bone-mvc-user/js/register.js"></script>
<link rel="stylesheet" href="/bone-mvc-user/css/password-strength.css" />
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png" />
                    <h1>Reset your password</h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <?php
                            if ($success) {?>
                                <p class="lead">Your password has been successfully changed.</p>
                                <a class="btn btn-success" href="/user/home">Continue</a>
                            <?php } else { ?>
                                <p class="lead">Please choose a good password. A combination of upper and lower case
                                    characters, symbols, and numbers makes for a strong password.</p>
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