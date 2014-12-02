<?php
$stmt = $dbh->query('SELECT name FROM Visibility');
$visibilitiesQuery = $stmt->fetchAll();

// State is not here cause it will start as open
// Not conclusion which is only activated when State is closed
echo '
<div id="poll">
<form action="processPollUpdate.php" method="post" enctype="multipart/form-data" onsubmit="return verifyQuestions();">
<div class="poll-info">
<label>Name * <input type="text" name="name" required="required" value="' . htmlentities($pollQuery['name'])  . '"></label>
<fieldset style="display: inline"><legend>Visibility *</legend>
';

foreach ($visibilitiesQuery as $key => $visibilityQuery) {
    echo "<label>{$visibilityQuery['name']} <input type=\"radio\" name=\"visibility\" value=\"{$visibilityQuery['name']}\" ";
    if (($key + 1) == $pollQuery['idVisibility']) {
        echo 'checked';
        $defaultCheckedRadio = $key;
    }
    echo '></label><br>';
}
echo '
</fieldset><label>Synopsis <textarea name="synopsis" cols="30" rows="6" placeholder="What is this study about">' . htmlentities($pollQuery['synopsis'])  . '</textarea></label>
<label>Image <input type="file" name="image"></label>
</div>
';

foreach ($questionsQuery as $key => $questionQuery) {
    echo '
    <div class="poll-question">
    <h2>Question ' . ($key + 1) . '</h2>
    <label>Description <textarea name="description[]" cols="30" rows="6" placeholder="Explain what this question is for">' . htmlentities($questionQuery['description']) . '</textarea></label><br>
    ';
    $decodedRadios = json_decode($questionQuery['options']);
    foreach ($decodedRadios as $subkey => $radio) {
        echo '<div><label>' . $radio . ' <input type="radio" name="option[' . $key . ']' . '[' . $subkey . ']" value="' . $radio . '" checked></label><input type="button" name="removeOption" value="remove"><br></div>';
    }
    echo '
    <label>Option Name <input type="text" name="nameOption"></label>
    <input type="button" name="addOption" value="Add Option">
    </div>
    ';
}
echo '<input type="button" name="addQuestion" value="Add Question">
<div class="poll-submit"><input type="hidden" name="csrf" value="' . $_SESSION['csrf_token'] .'">
<input type="submit" value="send" name="Send">
</div>
</form>
</div>';
?>
