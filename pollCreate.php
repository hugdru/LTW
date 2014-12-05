<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

// Only registered users can create enquiries
$loggedIn = validLogin();
if (!$loggedIn) {
    header('Location: index.php?err=login');
    exit();
}

require_once 'templates/header.php';?>
<main>
<?php
$stmt = $dbh->query('SELECT name FROM Visibility');
$visibility = $stmt->fetchAll();
?>
    <div id="poll">
        <form action="processPollCreation.php" method="post" enctype="multipart/form-data" onsubmit="return verifyQuestions();">
            <div class="poll-info">
                <label>Name * <input type="text" name="name" required="required"></label>
                <span id="errormsg_name" class="errormsg"></span>
                <fieldset style="display: inline"><legend>Visibility *</legend>
<?php
foreach ($visibility as $key => $value) {
    if ($key === 0) {
        echo    "<label>{$value['name']} <input type=\"radio\" name=\"visibility\" value=\"{$value['name']}\" checked></label><br>";
    } else {
        echo    "<label>{$value['name']} <input type=\"radio\" name=\"visibility\" value=\"{$value['name']}\"></label><br>";
    }
}
?>
                </fieldset>
                <label>Synopsis <textarea name="synopsis" cols="30" rows="6" placeholder="What is this study about"></textarea></label>
                <label>Image <input type="file" name="image"></label>
            </div>
            <div class="poll-question">
                <h2>Question 1</h2>
                <label>Description <textarea name="description[]" cols="30" rows="4" placeholder="Explain what this question is for" required></textarea></label>
                <br>
                <div>
                </div>
                <label>Option Name <input type="text" name="nameOption"></label>
                <input type="button" name="addOption" value="Add Option">
                <input type="button" name="removeQuestion" value="Remove Question">
            </div>
            <input type="button" name="addQuestion" value="Add Question">
            <div class="poll-submit">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']?>">
                <input type="submit" value="Submit" name="Send">
            </div>
        </form>
    </div>
</main>
<script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
<script type="text/javascript" src="javascript/pollCreateAndUpdate.js" defer></script>

<?php require_once 'templates/footer.php';?>
