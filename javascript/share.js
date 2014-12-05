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
      if (data == 'failed')
        alert('failed');
        else
          alert(data);
        }
      });
    }
