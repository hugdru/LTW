$().ready(loadDocument);

function loadDocument() {
    $('#poll').on('click', 'input[name="edit"]', redirectEdit);
    $('#visibility:contains("Public")').css("background", "green");
    $('#visibility:contains("Private")').css("background", "red");
    $('#state:contains("Open")').css("background", "blue");
    $('#state:contains("Closed")').css("background", "grey");
}

function redirectEdit() {
    var url = window.location.href + '&edit';
    window.location.replace(url);
}
