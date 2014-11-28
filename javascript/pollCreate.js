$().ready(loadDocument);

var regexOptionRadio = /\[(\d)]\[(\d+)]$/i;
var regexHeader = /^([\w\s]+)(\d+)$/;
var regexHeaderNumber = /(\d+)$/;

function loadDocument() {
    $('#poll').on('click', 'input[name="addOption"]', addOption);
    $('#poll').on('click', 'input[name="removeOption"]', removeOption);
    $('#poll').on('click', 'input[name="addQuestion"]', addQuestion);
    $('#poll').on('click', 'input[name="removeQuestion"]', removeQuestion);
}

function addOption() {

    var exit = false;
    var previous = $(this).prev();
    var optionName = previous.children('input').val();
    if (!optionName) return;

    var div = previous.prev();
    div.find('input[type="radio"]').each(
        function() {
            $this = $(this);
            if ($this.val() === optionName) {
                alert('There can\'t be two options with the same name');
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
            if (temp.length === 0) break;
            temp = temp.prev();
        }

        cloneNumber = temp.html().match(regexHeaderNumber) || [];
        cloneNumber = cloneNumber[1] - 1;
        subIndex = 0;
    }

    div.append(
        '<label>' + optionName + ' <input type="radio" name="option[' + cloneNumber + ']' + '[' + subIndex + ']" value="' + optionName + '" checked></label><input type="button" name="removeOption" value="remove"><br>'
    );

    previous.children('input').val('');
}

function removeOption() {
    var $this = $(this);

    $this.nextAll().children('input[type="radio"]').each(
        function() {
            $thiss = $(this);
            var match = $thiss.attr('name').match(regexOptionRadio) || [];
            $thiss.attr('name', 'option[' + match[1] + '][' + (match[2] - 1) + ']');
        }
    );

    $this.prev().remove();
    $this.next().remove();
    $this.remove();
}

function addQuestion() {
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
        ).end().find('div').children().remove().end().end()
    );

    if ($this.prev().find('input[name="removeQuestion"]').length === 0) {
        $this.prev().append('<input type="button" value="remove" name="removeQuestion">');
    }

}

function removeQuestion() {
    var $this = $(this).parent();
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
