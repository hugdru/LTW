$().ready(loadDocument);

function loadDocument() {
    $('#visibility:contains("Public")').css("background", "green");
    $('#visibility:contains("Private")').css("background", "red");
    $('#poll').on('click', 'input[name="edit"]', redirectEdit);
}

function redirectEdit() {
    var url = window.location.href + '&edit';
    window.location.replace(url);
}

function verifyRadios() {

    var noError = true;

    $('div.poll-question').each(
        function() {
            $this = $(this);
            if ($this.find('input[type="radio"]:checked').length === 0) {
                noError = false;
                if ($this.find('span').length === 0) {
                    $this.prepend('<span id="errormsg_name" class="errormsg">Missing option</span>');
                }
                $this.children('span').css('display', 'inline-block').delay(5000).fadeOut();
            }
        }
    );

    return noError;
}
