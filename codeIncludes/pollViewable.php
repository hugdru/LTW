<?php
echo '<div id="poll">';
if ($permission === 'editable') {
    echo '<input type="button" name="edit" value="edit">';
}
echo '
<div class="poll-info">
<div id="visibility">'. $visibility .'</div>
<h2>' . $pollQuery['name'] . '</h2>
<div id="state">' . $state . '</div>';

if ($pollQuery['synopsis']) {
    echo '<p id="synopsis">' . htmlentities($pollQuery['synopsis']) . '</p>';
}
if ($pollQuery['image']) {
    echo "<img src=\"images/{$pollQuery['idUser']}/{$pollQuery['idPoll']}/{$pollQuery['image']}\" alt=\"\">";
}
echo '</div>';
foreach ($questionsQuery as $key => $questionQuery) {
    echo '<div class="poll-question">';
    if ($questionQuery['description']) {
        echo '<h3>' . htmlentities($questionQuery['description']) . '</h3>';
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
    echo '</table></div></div>';
}
echo '</div></div>';
?>
