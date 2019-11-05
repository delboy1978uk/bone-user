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
                    <h1>Edit your profile</h1>
                    <?= isset($message) ? $this->alert($message) : '' ?>
                    <div id="alert" class="alert alert-info alert-dismissible" role="alert">
                        You can set your username here, and upload your image (or choose one of ours).
                    </div>
                    <form action="" method="post">
                        <div id="existing-avatar" class="<?= $p->getImage() ? null : 'hidden'; ?>">
                            <img id="my-avatar" src="<?= $p->getImage(); ?>" alt="<?= $p->getAka(); ?>"
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


                    </form>
                    <?= $form ?>
                    <a id="home-button" href="/"
                                   class="btn btn-lg btn-success <?= ($p->getImage() && $p->getAka()) ? null : 'disabled'; ?> pull-right">Home</a>

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
            } else if (log) {
                alert(log);
            }

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
            var replace = location.protocol + '//' + location.host;
            var avatar = src.replace(replace, '');
            $.post('/api/user/choose-avatar', {avatar: avatar}, function (result) {
                var alertbox = $('#alert');
                alertbox.removeClass('alert-danger');
                alertbox.removeClass('alert-info');
                alertbox.removeClass('alert-success');
                alertbox.addClass('alert-' + result.result);
                alertbox.html(result.message);
                if (result.result == 'success') {
                    set_avatar = result.avatar;
                    $('#image').val(set_avatar);
                    $('#user-avatar').prop('src',  set_avatar);
                    $('#my-avatar').prop('src',  set_avatar);
                    $('#existing-avatar').removeClass('hidden');
                    $('#change-existing').addClass('hidden');
                }
            });
        });


        // AJAX UPLOAD
        $('#upload').click(function (e) {
            var jform = new FormData();
            jform.append('avatar', $('#avatar').get(0).files[0]);

            $.ajax({
                url: '/api/user/upload-avatar',
                type: 'POST',
                data: jform,
                dataType: 'json',
                mimeType: 'multipart/form-data',
                contentType: false,
                cache: false,
                processData: false,
                success: function (result) {
                    var alertbox = $('#alert');
                    alertbox.removeClass('alert-danger');
                    alertbox.removeClass('alert-info');
                    alertbox.removeClass('alert-success');
                    alertbox.addClass('alert-' + result.result);
                    alertbox.html(result.message);
                    if (result.result == 'success') {
                        set_avatar = result.avatar
                        $('#image').val(set_avatar);
                        $('#my-avatar').prop('src', '/download?file=' + set_avatar).addClass('img-circle');
                        $('#user-avatar').prop('src', '/img/' + set_avatar);
                        $('#existing-avatar').removeClass('hidden');
                        $('#change-existing').addClass('hidden');
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