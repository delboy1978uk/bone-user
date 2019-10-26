<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png" />
                    <h1>Change your email</h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <div class="page-scroll">
                        <div class="well" style="color: black;">
                            <p class="lead">
                                Email address changed.
                            </p>
                            <a href="/user/home" class="btn btn-success">Continue</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>