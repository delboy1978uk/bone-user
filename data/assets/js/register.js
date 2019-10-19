$(document).ready(function () {

    var submit = $('#submit');

    var text = $('#password_text');
    submit.prop('disabled',true);

    $('.password').pstrength();

    $('#confirm').keyup(function(e){

        var password = $('#password').val();
        var confirm = $('#confirm').val();
console.log(password);
console.log(confirm);

        if(password == confirm) {
            submit.prop('disabled',false);
            text.html('<span style="color:#0f0">Your passwords match!</span>');
        } else {
            submit.prop('disabled',true);
            text.html('<span style="color:#f00">Your passwords must match!</span>');
        }
    });


});