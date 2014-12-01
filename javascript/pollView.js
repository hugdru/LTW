$().ready(loadDocument);

function loadDocument() {
    $('#poll').on('click', 'input[name="edit"]', redirectEdit);
}

function redirectEdit() {
    var url = window.location.href + '&edit';
    window.location.replace(url);
}
