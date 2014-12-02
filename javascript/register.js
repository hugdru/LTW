$().ready(loadDocument);

function loadDocument() {
  $('#emailReg').on('change', verifyEmail);
  $('#usernameReg').on('change', verifyUsername);
}

function verifyEmail() {
  $.ajax({
    type: "POST",
    url: "ajaxIncludes/search.php",
    data: {'column':'email', 'value':$('#emailReg').val()},
    success: function(data) {
      if(data == 'success')
        $('#errormsg_email').show();
      else
        $('#errormsg_username').hide();
      }
  });
}

function verifyUsername() {
  $.ajax({
    type: "POST",
    url: "ajaxIncludes/search.php",
    data: {'column':'username', 'value':$('#usernameReg').val()},
    success: function(data) {
      if(data == 'success')
        $('#errormsg_username').show();
      else
        $('#errormsg_username').hide();
    }
  });
}
