$().ready(loadDocument);

function loadDocument() {
  $('#visibility:contains("Public")').css("background", "green");
  $('#visibility:contains("Private")').css("background", "red");
  $('#state:contains("Open")').css("background", "blue");
  $('#state:contains("Closed")').css("background", "grey");
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
