<section class="intro">
    <div class="pt50">
        <div class="container">
            <?= null !== $message ? $this->alert($message) : null ?>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <img alt="Logo" src="/img/skull_and_crossbones.png" />
                    <h1>Welcome, <?= $user->getEmail() ?></h1>
                    <p class="lead">This is the logged in user's home page.</p>
                    <a class="btn btn-success" href="/user/edit-profile">Edit Profile</a>
                    <a class="btn btn-primary" href="/user/change-email">Change Email</a>
                    <a class="btn btn-warning" href="/user/change-password">Change Password</a>
                    <a class="btn btn-danger" href="/user/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
</section>
