<?php

use Del\Icon;

/** @var Del\Entity\Person $p */
$p = $person;
?>
<section class="intro">
    <div class="">
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <img src="/img/skull_and_crossbones.png"/>
                    <h1>Edit your profile</h1>
                    <?= null !== $message ? $this->alert($message) : '' ?>
                    <p class="lead">You can set your username here, and upload your image <br>(or choose one of ours).</p>
                    <form action="" method="post">


                        <label for="username">Username</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="aka" name="aka" value="<?= $p->getAka(); ?>"/>
                            <span class="input-group-btn">
                                <span id="check-aka" class="btn btn-<?= $p->getAka() ? 'success' : 'primary'; ?> group-end disabled"><?= $p->getAka() ? Icon::CHECK_CIRCLE : 'Check..'; ?></span>
                            </span>
                        </div>

                        <label for="avatar">Avatar</label>
                        <div id="existing-avatar" class="<?= $p->getImage() ? null : 'hidden'; ?>">
                            <img id="my-avatar" src="/img/<?= $p->getImage(); ?>" alt="<?= $p->getAka(); ?>"
                                 class="m20 img-circle"/>
                            <button id="change-avatar" type="button" class="btn btn-primary">Change my avatar</button>
                        </div>

                        <div id="change-existing" class="<?= $p->getImage() ? 'hidden' : null; ?>">
                            <div class="btn-group btn-block nav" role="group" aria-label="...">
                                <button id="choose-pic" type="button" class="btn btn-default disabled">Choose an
                                    avatar
                                </button>
                                <button id="upload-pic" type="button" class="btn btn-primary">Upload my own</button>
                            </div>


                            <div id="choose-avatar">
                                <p class="lead tc mt20">Choose a picture</p>
                                <div class="row">
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/dog.png" alt="Dog"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/cat.png" alt="Cat"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/gorilla.png" alt="Gorilla"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/lion.png" alt="Lion"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/koala.png" alt="Koala"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/rabbit.png" alt="Rabbit"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20">
                                        <img src="/bone-mvc-user/img/avatars/tiger.png" alt="Tiger"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                    <div class="col-md-3 tc mb20 ">
                                        <img src="/bone-mvc-user/img/avatars/fox.png" alt="Fox"
                                             class="img-responsive centered avatar"/>
                                    </div>
                                </div>
                                <p><small>These great animal avatars were provided by Freepik(<a
                                                target="_blank"
                                                href="//www.freepik.com/free-photos-vectors/design">Design
                                            vector designed by Freepik</a>)</small></p>
                            </div>


                            <div id="upload-my-own" class="hidden mt20">
                                <p class="lead tc mt20">Select and upload a picture</p>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <span class="btn btn-primary btn-file">
                                            Browse  <input type="file" name="avatar" id="avatar"/>
                                        </span>
                                    </span>
                                    <input type="text" class="form-control" readonly/>
                                    <span class="input-group-btn">
                                        <span id="upload" class="btn btn-primary group-end disabled">Upload..</span>
                                    </span>
                                </div>
                            </div>
                        </div>


                        <br>
                        <a id="home-button" href="/"
                           class="btn btn-lg btn-success <?= ($p->getImage() && $p->getAka()) ? null : 'disabled'; ?> pull-right">Home</a>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php /** @var Del\Entity\Person $p */
$p = $person; ?>


<script type="text/javascript">

    // BOOTSTRAP STYLE FILE INPUT
    $(document).on('change', '.btn-file :file', function () {
        var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });

    $(document).ready(function () {


        // BOOTSTRAP STYLE FILE INPUT
        $('.btn-file :file').on('fileselect', function (event, numFiles, label) {

            var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

            if (input.length) {
                input.val(log);
                $('#upload').removeClass('disabled');
            } else {
                if (log) alert(log);
            }

        });


        // SET USERNAME
        var set_aka = '<?= $p->getAka();?>';
        $('#aka').on('keyup', function (e) {
            var aka = $(this).val();
            if (aka) {
                if (aka == set_aka) {
                    $('#check-aka')
                        .html('<?= Icon::CHECK_CIRCLE; ?>')
                        .removeClass('btn-primary')
                        .addClass('btn-success disabled');
                } else {
                    $('#check-aka')
                        .removeClass('disabled btn-success')
                        .addClass('btn-primary')
                        .html('Check..');
                }
            } else {
                $('#check-aka').addClass('disabled btn-primary');
            }
        });

        $('#check-aka').click(function () {
            $(this).html('<?= Icon::custom(Icon::REFRESH, 'fa-spin');?>');
            var aka = $('#aka').val();
            $.post('/profile/set-aka', {aka: aka}, function (result) {
                var alertbox = $('#alert');
                alertbox.removeClass('alert-danger');
                alertbox.removeClass('alert-info');
                alertbox.removeClass('alert-success');
                alertbox.addClass('alert-' + result.result);
                alertbox.html(result.message);
                if (result.result == 'success') {
                    $('#check-aka')
                        .html('<?= Icon::CHECK_CIRCLE; ?>')
                        .removeClass('btn-primary').addClass('btn-success disabled');
                    $('span#identity').html(aka);
                    set_aka = aka;
                    if (set_avatar) {
                        $('#home-button').removeClass('disabled');
                    }
                }
            });
        });


        // Choose Avatar or Upload Image
        var set_avatar = '<?= $p->getImage();?>';
        $('#upload-pic').click(function (e) {
            $(this).addClass('disabled');
            $('#choose-pic').removeClass('disabled').removeClass('btn-default').addClass('btn-primary');
            $('#choose-avatar').addClass('hidden');
            $('#upload-my-own').removeClass('hidden');
        });
        $('#choose-pic').click(function (e) {
            $(this).addClass('disabled');
            $('#upload-pic').removeClass('disabled').removeClass('btn-default').addClass('btn-primary');
            $('#upload-my-own').addClass('hidden');
            $('#choose-avatar').removeClass('hidden');
        });


        // Change Avatar
        $('#change-avatar').click(function () {
            $('#existing-avatar').addClass('hidden');
            $('#change-existing').removeClass('hidden');
        });


        // Choose Avatar
        $('img.avatar').click(function () {
            var src = $(this).prop('src');
            var replace = location.protocol + '//' + location.host + '/img/';
            var avatar = src.replace(replace, '');
            $.post('/profile/choose-avatar', {avatar: avatar}, function (result) {
                console.log(result);
                var alertbox = $('#alert');
                alertbox.removeClass('alert-danger');
                alertbox.removeClass('alert-info');
                alertbox.removeClass('alert-success');
                alertbox.addClass('alert-' + result.result);
                alertbox.html(result.message);
                if (result.result == 'success') {
                    set_avatar = result.avatar;
                    $('#user-avatar').prop('src', '/img/' + set_avatar);
                    $('#my-avatar').prop('src', '/img/' + set_avatar);
                    $('#existing-avatar').removeClass('hidden');
                    $('#change-existing').addClass('hidden');
                    if (set_aka) {
                        $('#home-button').removeClass('disabled');
                    }
                }
            });
        });


        // AJAX UPLOAD
        $('#upload').click(function (e) {
            var jform = new FormData();
            jform.append('avatar', $('#avatar').get(0).files[0]);

            $.ajax({
                url: '/profile/upload-avatar',
                type: 'POST',
                data: jform,
                dataType: 'json',
                mimeType: 'multipart/form-data',
                contentType: false,
                cache: false,
                processData: false,
                success: function (result) {
                    console.log(result);
                    var alertbox = $('#alert');
                    alertbox.removeClass('alert-danger');
                    alertbox.removeClass('alert-info');
                    alertbox.removeClass('alert-success');
                    alertbox.addClass('alert-' + result.result);
                    alertbox.html(result.message);
                    if (result.result == 'success') {
                        set_avatar = result.avatar
                        $('#my-avatar').prop('src', '/img/' + set_avatar).addClass('img-circle');
                        $('#user-avatar').prop('src', '/img/' + set_avatar);
                        $('#existing-avatar').removeClass('hidden');
                        $('#change-existing').addClass('hidden');
                        if (set_aka) {
                            $('#home-button').removeClass('disabled');
                        }
                    }
                },
                error: function (jqXHR, status, error) {
                    // Hopefully we should never reach here
                    console.log(jqXHR);
                    console.log(status);
                    console.log(error);
                }
            });
        });
    });
</script>