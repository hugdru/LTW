$().ready(loadDocument);

function loadDocument() {
  $('#submit_share').click(shareEmail);
}

function shareEmail() {
  $.ajax({
    type: 'POST',
    url: 'codeIncludes/share.php',
    data: {'email': $('#email').val(), 'url': $('#url').val()},
    success: function(data) {
      $('#share_result').html(data);
    }
  });
}
