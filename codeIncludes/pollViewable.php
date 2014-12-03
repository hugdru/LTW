<?php
echo '<div id="poll">';
if ($permission === 'editable') {
    echo '<input type="button" name="edit" value="edit">';
}
echo '
<div class="poll-info">
<h2>' . $pollQuery['name'] . '</h2>
<p><span class="fields">Visibility: </span>' . $visibility . '</p>
<p><span class="fields">State: </span>' . $state . '</p>
';

if ($pollQuery['synopsis']) {
    echo '<h3>Synopsis</h3><p>' . htmlentities($pollQuery['synopsis']) . '</p>';
}
if ($pollQuery['image']) {
    echo "<img src=\"images/{$pollQuery['idUser']}/{$pollQuery['idPoll']}/{$pollQuery['image']}\" alt=\"\">";
}
echo '</div>';
foreach ($questionsQuery as $key => $questionQuery) {
    echo '<div class="poll-question"><h3>Question ' . ($key + 1) . '</h3>';
    if ($questionQuery['description']) {
        echo '<p>' . htmlentities($questionQuery['description']) . '</p>';
    }
    $decodedRadios = json_decode($questionQuery['options'], true);
    $decodedResult = json_decode($questionQuery['result'], true);

    $total = 0;
    foreach ($decodedResult as $dr_) {
        $total += $dr_;
    }

    echo '<div><table>';

    foreach ($decodedRadios as $key => $decodedRadio) {
        if ($total == 0) {
            $percenRadio = 0;
        } else {
            $percenRadio = 100 * round($decodedResult[$key]/$total, 2);
        }
        echo '<tr><td>' . $decodedRadio . '</td><td><img src="resources/images/poll.gif" width="' . $percenRadio . '" height="20" alt="">' . $percenRadio . '&percnt;</td></tr>';
    }
    echo '</table></div>';
}
echo '</div></div></div>';
?>
