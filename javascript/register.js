$().ready(loadDocument);

var globalTimeout = null;

function loadDocument() {
  $('#emailReg').change(verifyEmail);
  $('#usernameReg').change(verifyUsername);
  $('#search_bar').keyup( function(){
      if(globalTimeout != null)
        clearTimeout(globalTimeout);
      globalTimeout = setTimeout(getPolls, 300);
    }
  );
}

function verifyEmail() {
  $.ajax({
    type: "POST",
    url: "../ajaxIncludes/search.php",
    data: {'column':'email', 'value':$('#emailReg').val()},
    success: function(data) {
      if(data == 'failed')
        $('#errormsg_email').hide();
      else
        $('#errormsg_username').show();
      }
  });
}

function verifyUsername() {
  $.ajax({
    type: "POST",
    url: "search.php",
    data: {'column':'username', 'value':$('#usernameReg').val()},
    success: function(data) {
      if(data == 'failed')
        $('#errormsg_username').hide();
      else
        $('#errormsg_username').show();
    }
  });
}

function getPolls() {
  globalTimeout = null;
  $.ajax({
    type: "POST",
    url: "search.php",
    data: {'column':'pollName', 'value':$('#search_bar').val()},
    success: function(data) {
      if(data == 'failed')
          $('#search_results').html("");
      else
          $('#search_results').html(data);
      }
  });
}
