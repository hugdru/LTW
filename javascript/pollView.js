$().ready(loadDocument);

function loadDocument() {
    $('#poll').on('click', 'input[name="edit"]', redirectEdit);
    $('#visibility:contains("Public")').css("background", "green");
    $('#visibility:contains("Private")').css("background", "red");
}

function redirectEdit() {
    var url = window.location.href + '&edit';
    window.location.replace(url);
}
