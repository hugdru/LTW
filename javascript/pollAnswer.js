$().ready(loadDocument);

function loadDocument() {
    //$('#poll').on('click', 'input[type="radio"]', showResult);
}

function verifyRadios() {
    var noError = true;
    var $questionNumber = 1;

    $('div.poll-question').each(
        function() {
            $this = $(this);
            if ($this.find('input[type="radio"]:checked').length === 0) {
                noError = false;
                alert('You have to select an option in Question ' + $questionNumber);
                return;
            }
            ++$questionNumber;
        }
    );

    return noError;
}

