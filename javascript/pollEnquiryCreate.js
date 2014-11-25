$().ready(loadDocument);

function loadDocument() {
  $("#pollEnquiry input[name='addOption']").click(addOption);
  $("#pollEnquiry input[name='addPoll']").click(addPoll);
}

function addOption() {
    var previous = $(this).prev();
    if (!previous.children('input').val()) return false;

    previous.before('<label>' + previous.children('input').val() + ' <input type="radio" name="option" value="' + previous.children('input').val() + '"></label><br>');
    return true;
}

function addPoll() {
    var previous = $(this).prev();
    $(this).before(previous.clone());
}
