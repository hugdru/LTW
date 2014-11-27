$().ready(loadDocument);

var regexOptionRadio = /^(\w)\[(\d)+\]\[\]$/;
var regexHeader = /^([\w\s]+)(\d)+/;
var regexHeaderNumber = /(\d)+\s*$/;

function loadDocument() {
    $('#pollEnquiry').on('click', 'input[name="addOption"]', addOption);
    $('#pollEnquiry').on('click', 'input[name="removeOption"]', removeOption);
    $('#pollEnquiry').on('click', 'input[name="addPoll"]', addPoll);
    $('#pollEnquiry').on('click', 'input[name="removePoll"]', removePoll);
    $('#pollEnquiry').on('click', 'input[name="nameOption"]', function() {$(this).val('')});
}

function addOption() {

    var temp;
    var previous = $(this).prev();
    if (!previous.children('input').val()) return;

    temp = previous;
    while (!temp.is('h2')) {
        if (temp.length === 0) break;
        temp = temp.prev();
    }

    var cloneNumber = temp.html().match(regexHeaderNumber);

    previous.prev().append(
        '<label>' + previous.children('input').val() + ' <input type="radio" name="option[' + cloneNumber[1] + '][]" value="' + previous.children('input').val() + '" disabled></label><input type="button" name="removeOption" value="remove"><br>'
    );

}

function removeOption() {
    var $this = $(this);

    $this.prev().remove();
    $this.next().remove();
    $this.remove();
}

function addPoll() {
    var $this = $(this);
    var previous = $this.prev();
    $this.before(previous.clone().
    find('h2').each
            (
                function() {
                    var $thiss = $(this);
                    var match = $thiss.html().match(regexHeader) || [];
                    if (match.length === 3) {
                        $thiss.html(match[1] + (Number(match[2]) + 1));
                    }
                }
            ).end().find('div').children().remove().end().end()
    );

//.append(
            //'<input type="button" value="remove" name="removePoll">')

}

function removePoll() {
    $(this).parents('.pollEnquiry-poll').remove();
}
