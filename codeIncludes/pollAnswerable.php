<?php
echo '
<div id="poll">';
if ($isOwner) {
    echo '<input type="button" name="edit" value="edit">';
}
echo '<div class="poll-info">
<h2><span id="visibility">' . $visibility . '</span> ' . $pollQuery['name'] . '</h2>
<label>Current Poll State:<div id="state">'. $state . ' </div> </div></label>';

if ($pollQuery['synopsis']) {
    echo '<p id="synopsis">' . htmlentities($pollQuery['synopsis'])  . '</p>';
}
if ($pollQuery['image']) {
    echo "<img src=\"images/{$pollQuery['idUser']}/{$pollQuery['idPoll']}/{$pollQuery['image']}\" alt=\"\">";
}
echo '
</div>
<form action="processPollAnswer.php" method="post" onsubmit="return verifyRadios();">
';
$optionNum = 1;
foreach ($questionsQuery as $key => $questionQuery) {
    echo '<div class="poll-question">'; //<h3>Question' . ($key + 1) . '</h3>';
    if ($questionQuery['description']) {
        echo '<h3>' . htmlentities($questionQuery['description']) . '</h3>';
    }
    $decodedRadios = json_decode($questionQuery['options'], true);
    echo '<div>';
    foreach ($decodedRadios as $decodedRadio) {
        echo "<input type=\"radio\" id=\"radio$optionNum\" name=\"option[$key]\" value=\"$decodedRadio\"><label for=\"radio$optionNum\">$decodedRadio</label><br>";
        $optionNum++;
    }
    echo '</div></div>';
}
echo '
<div class="poll-submit">
<input type="hidden" name="csrf" value="' . $_SESSION['csrf_token'] . '">
<input type="hidden" name="pollId" value="' . $pollId . '">
<input type="hidden" name="mode" value="' . $mode . '">
<input type="submit" value="Submit" name="Send">
</div></form></div>
';
?>
