<?php
echo '
<div id="poll">
<div class="poll-info">
<h2>' . $pollQuery['name'] . '</h2>
<p><span class="fields">Visibility: </span>' . $visibility . '</p>
<p><span class="fields">State: </span>' . $state . '</p>
';

if ($pollQuery['synopsis']) {
    echo '
<h3>Synopsis</h3>
<p>' . htmlentities($pollQuery['synopsis'])  . '</p>
    ';
}
if ($pollQuery['image']) {
    echo "
<img src=\"images/{$pollQuery['idUser']}/{$pollQuery['idPoll']}/{$pollQuery['image']}\" alt=\"\">
    ";
}
echo '
</div>
<form action="processPollAnswer.php" method="post" onsubmit="return verifyRadios();">
';

foreach ($questionsQuery as $key => $questionQuery) {
    echo '<div class="poll-question"><h3>Question' . ($key + 1) . '</h3>';
    if ($questionQuery['description']) {
        echo '<p>' . htmlentities($questionQuery['description']) . '</p>';
    }
    $decodedRadios = json_decode($questionQuery['options'], true);
    echo '<div>';
    foreach ($decodedRadios as $decodedRadio) {
        echo "<label>$decodedRadio <input type=\"radio\" name=\"option[$key]\" value=\"$decodedRadio\"></label><br>";
    }
    echo '</div></div>';
}
echo '
<div class="poll-submit">
<input type="hidden" name="csrf" value="' . $_SESSION['csrf_token'] . '">
<input type="hidden" name="pollId" value="' . $pollId . '">
<input type="hidden" name="mode" value="' . $mode . '">
<input type="submit" value="send" name="Send">
</div></form></div>
';
?>
