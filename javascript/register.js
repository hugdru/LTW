$().ready(loadDocument);

var passwordRegex = [[/[0-9]+/, 'number'], [/[a-z]+/, 'lower case letter'], [/[A-Z]+/, 'upper case letter'], [/\W+/, 'symbol']];
var usernameRegex = [[/^[a-z]/i, 'first character must be a letter'], [/^[a-z][a-z0-9\.\-_]{3,19}$/i, 'the following characters must be a letter, number, dot, hyphen or underscore']];

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
    $('#search_bar').load(getPolls());
    $('#search_type').change(getPolls);

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

    $this = $(this);

    var error = '';

    username = $this.val();

    if (username.length < 4) {
        error += 'at least 4 characters';
    }
    if (username.length > 20) {
        error += 'at most 20 characters';
    }

    var usernameRegexLength = usernameRegex.length;
    for (var i = 0; i < usernameRegexLength; ++i) {
        if (!username.match(usernameRegex[i][0])) {
            if (error === '') {
                error = error + usernameRegex[i][1];
            } else {
                error = error + ', ' + usernameRegex[i][1];
            }
        }
    }

    if (error === '') {
        $this.removeClass('invalid').next().hide();
    } else {
        $this.addClass('invalid').next().html(error).css('display', 'inline-block');
        return;
    }

    $.ajax({
            type: 'POST',
            url: 'search.php',
            data: {'column': 'username', 'value': username},
            success: function(data) {
                if (data == 'failed')
                    $this.removeClass('invalid').next().hide();
                else
                    $this.addClass('invalid').next().html('username already exists').css('display', 'inline-block');
            }
    });
}

function verifyPassword() {

    $this = $(this);

    var error = '';

    password = $this.val();

    if (password.length < 8) {
        error += 'at least 8 characters';
    } else if (password.length > 72) {
        error += 'at most 72 characters';
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
    data: {'column': $('#search_type').val(), 'value': $('#search_bar').val()},
    success: function(data) {
      if (data == 'failed')
        $('#search_results').html('');
        else
          $('#search_results').html(data);
        }
      });
    }
