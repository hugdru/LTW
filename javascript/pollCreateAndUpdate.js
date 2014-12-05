$().ready(loadDocument);

var regexOptionRadio = /\[(\d)]\[(\d+)]$/i;
var regexHeader = /^([\w\s]+)(\d+)$/;
var regexHeaderNumber = /(\d+)$/;

var issueResetWarning = false;

function loadDocument() {
    $('#poll').on('click', 'input[name="addOption"]', addOption);
    $('#poll').on('click', 'input[name="removeOption"]', removeOption);
    $('#poll').on('click', 'input[name="addQuestion"]', addQuestion);
    $('#poll').on('click', 'input[name="removeQuestion"]', removeQuestion);
    $('#poll').on('change', 'input[name="name"]', verifyName);
    $('#poll').on('keyup keypress',
        function(e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        }
    );
    $('#poll').on('keypress', 'input[name="nameOption"]', enterOptionOnEnter);
    $('#poll').on('change', 'textarea', function() { issueResetWarning = true; });
}

function addOption() {
    addOptionAux($(this));
}

function addOptionAux(previous) {

    issueResetWarning = true;

    var exit = false;
    var option = previous.prev().children('input');
    var optionName = option.val();
    if (!optionName) return;

    var div = previous;

    while (!div.is('div')) {
        if (div.length === 0) return;
        div = div.prev();
    }

    div.find('input[type="radio"]').each(
        function() {
            $this = $(this);
            if ($this.val() === optionName) {

                parent = $this.parent();

                if (parent.nextUntil('br').filter('span').length === 0) {
                    parent.next().after('<span class="errormsg">Option already exists</span>');
                }
                parent.nextAll('span.errormsg').first().css('display', 'inline-block').delay(2000).fadeOut();

                exit = true;
                return;
            }
        }
    );
    if (exit) {
        previous.children('input').val('');
        return;
    }

    var match;

    var nameContent = div.find('input[type="radio"]').last().attr('name');

    var cloneNumber;
    var subIndex;
    if (nameContent) {
        match = nameContent.match(regexOptionRadio) || [];
        cloneNumber = match[1];
        subIndex = Number(match[2]) + 1;
    } else {
        var temp = previous;

        while (!temp.is('h2')) {
            if (temp.length === 0) return;
            temp = temp.prev();
        }

        cloneNumber = temp.html().match(regexHeaderNumber) || [];
        cloneNumber = cloneNumber[1] - 1;
        subIndex = 0;
    }

    div.append(
        '<label>' + optionName + ' <input type="radio" name="option[' + cloneNumber + ']' + '[' + subIndex + ']" value="' + optionName + '" checked></label><input type="button" name="removeOption" value="remove"><br>'
    );

    option.val('');
}

function removeOption() {

    issueResetWarning = true;

    var $this = $(this);

    $this.nextAll().children('input[type="radio"]').each(
        function() {
            $thiss = $(this);
            var match = $thiss.attr('name').match(regexOptionRadio) || [];
            $thiss.attr('name', 'option[' + match[1] + '][' + (match[2] - 1) + ']');
        }
    );

    $this.prev().remove();
    $this.nextUntil('br').remove();
    $this.next().remove();
    $this.remove();
}

function addQuestion() {

    issueResetWarning = true;

    var $this = $(this);
    var previous = $this.prev();
    $this.before(previous.clone().
    find('h2').each(
            function() {
                var $thiss = $(this);
                var match = $thiss.html().match(regexHeader) || [];
                if (match.length === 3) {
                    $thiss.html(match[1] + (Number(match[2]) + 1));
                }
            }
        ).end().find('span').remove().end().find('div').children().remove().end().end()
    );

    if ($this.prev().find('input[name="removeQuestion"]').length === 0) {
        $this.prev().append('<input type="button" value="Remove Question" name="removeQuestion">');
    }

}

function removeQuestion() {

    issueResetWarning = true;

    var $this;

    if ((pollQuestions = $('.poll-question')).length === 1) {
        $this = $(this);
        if ($this.nextAll('span.errormsg').length === 0) {
            $this.after('<span id="errormsg_name" class="errormsg">Must have at least one Question</span>');
        }
        $this.next().css('display', 'inline-block').delay(2000).fadeOut();
        return;
    }

    $this = $(this).parent();

    $this.nextAll('.poll-question').each(
        function() {
            var $this = $(this);
            var $thisHeader = $this.find('h2').first();
            var match = $thisHeader.html().match(regexHeader) || [];
            if (match.length === 3) {
                $thisHeader.html(match[1] + (Number(match[2]) - 1));
            }

            $this.find('input[type="radio"]').each(
                function() {
                    var $thiss = $(this);
                    var match = $thiss.attr('name').match(regexOptionRadio) || [];
                    if (match.length === 3) {
                        $thiss.attr('name', 'option[' + (match[1] - 1) + '][' + match[2] + ']');
                    }
                }
            );
        }
    );

    $this.remove();
}

function verifyQuestions() {

    var noError = true;

    $('div.poll-question').each(
        function() {
            $this = $(this);
            if ($this.find('input[type="radio"]').length < 2) {
                noError = false;
                heading = $this.find('h2');
                if (heading.prev('span').length === 0) {
                    heading.before('<span id="errormsg_name" class="errormsg">Missing option</span>');
                }
                heading.prev('span').css('display', 'inline-block').delay(5000).fadeOut();
            }
        }
    );

    if (noError && issueResetWarning && ($('#editMode').length > 0)) {
        var answer = confirm('Because you changed a question the poll results will be reset. Do you wish to continue?');
        if (answer == true) {
            noError = true;
        } else {
            noError = false;
        }
    }

    return noError;
}

function verifyName() {

    $this = $(this);

    var error = '';

    name = $this.val();

    if (name.length < 5) {
        error += 'at least 5 characters';
    } else if (name.length > 100) {
        error += 'at most 100 characters';
    }

    if (!name.match(/^[^0-9]{5}/)) {
        if (error === '') {
            error += "first 5 characters can't be numbers";
        } else {
            error += ", first 5 characters can't be numbers";
        }
    }

    if (error === '') {
        $this.removeClass('invalid').parent().next().hide();
    } else {
        $this.addClass('invalid').parent().next().html(error).css('display', 'inline-block');
    }
}

function enterOptionOnEnter(key) {

    if (key.which === 13) {
        addOptionAux($(this).parent().next());
    }
}
