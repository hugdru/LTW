$().ready(loadDocument);

var passwordRegex = [[/[0-9]+/, 'number'], [/[a-z]+/, 'lower case letter'], [/[A-Z]+/, 'upper case letter'], [/\W+/, 'symbol']];

var globalTimeout = null;

function loadDocument() {
    $('#emailReg').change(verifyEmail);
    $('#usernameReg').change(verifyUsername);
    $('form input[name="password"]').change(verifyPassword);
    $('form input[name="passwordAgain"]').change(verifyPasswords);
    $('#search_bar').keyup(function() {
        if (globalTimeout !== null)
            clearTimeout(globalTimeout);
        globalTimeout = setTimeout(getPolls, 300);
    }
    );
}

function verifyEmail() {
    $.ajax({
            type: 'POST',
            url: 'search.php',
            data: {'column': 'email', 'value': $('#emailReg').val()},
            success: function(data) {
                if (data == 'failed')
                    $('#errormsg_email').hide().prev().removeClass('invalid');
                else
                    $('#errormsg_email').css('display', 'inline-block').prev().addClass('invalid');
            }
    });
}

function verifyUsername() {
    $.ajax({
            type: 'POST',
            url: 'search.php',
            data: {'column': 'username', 'value': $('#usernameReg').val()},
            success: function(data) {
                if (data == 'failed')
                    $('#errormsg_username').hide().prev().removeClass('invalid');
                else
                    $('#errormsg_username').css('display', 'inline-block').prev().addClass('invalid');
            }
    });
}

function verifyPassword() {

    $this = $(this);

    var error = '';

    password = $this.val();

    if (password.length < 8) {
        error += '8 characters';
    }

    var passwordRegexLength = passwordRegex.length;
    for (var i = 0; i < passwordRegexLength; ++i) {
        if (!password.match(passwordRegex[i][0])) {
            if (error === '') {
                error = error + passwordRegex[i][1];
            } else {
                error = error + ', ' + passwordRegex[i][1];
            }
        }
    }

    if ($('#passwordAgainReg').val() !== '') {
        verifyPasswords();
    }

    if (error === '') {
        $this.removeClass('invalid').next().hide();
    } else {
        $this.addClass('invalid').next().html(error).css('display', 'inline-block');
    }
}

function verifyPasswords() {

    passwordAgain = $('#passwordAgainReg');
    password = $('#passwordReg');

    if (passwordAgain.val() === password.val()) {
        passwordAgain.removeClass('invalid').next().hide();
    } else {
        passwordAgain.addClass('invalid').next().html("Passwords don't match").css('display', 'inline-block');
    }
}

function getPolls() {
    globalTimeout = null;
    $.ajax({
            type: 'POST',
            url: 'search.php',
            data: {'column': 'pollName', 'value': $('#search_bar').val()},
            success: function(data) {
                if (data == 'failed')
                    $('#search_results').html('');
                else
                    $('#search_results').html(data);
            }
    });
}
