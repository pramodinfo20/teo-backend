<?php
/**
 * @author pramod jayaramaiah
 * feedback.php
 * Mitarbeitern Feedack
 */
?>

<!-- Form is created by auto fetching the username, useremail, rolle selected and page link.
     The page values is sent to success.php page   -->

<div class="inner_container">
    
    <div class="row ">
        <div class="twelve columns padding_all">
            <h1>Feedback Form</h1><br>
            <?php 
            $name = $user->getUserFullName(); 
            $email = $user->getUserEmail();
            $role_selected = $this->userPtr->getUserRoleLabel();
            $url = $_SERVER["HTTP_REFERER"];
            $url_components = parse_url($url);
            parse_str($url_components['query'], $params);
            // $link = $url_components['query'];
           // echo $link;
           $link = $params['action'];
           // echo $params['action'];
             $_SESSION['action'] = $_SERVER["HTTP_REFERER"];
            // echo $_SESSION['action'];
            $content_html = <<<HEREDOC
                <div>
                    <form method="post" id="feedback_insert_form" action="?page=success" class="aktuelle">
                        <p class="label"><label for="title-0">Subject:</label>
                        &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
                        <select class="deputy_selector" name="subject" id="deputy-0" required>
                            <option value="">Bitte wählen</option>
                            <option value="Suggestion">Suggestion</option>
                            <option value="Improvements">Improvements</option>
                        </select> </p>
                         <p><input type="hidden" name="name" value="{$name}" required></p>
                         <p><input type="hidden" name="mail" value="{$email}" required></p>
                         <p><input type="hidden" name="title" value="{$role_selected}" required></p>
                        <p class="label"><label for="title-0">Feedback eingeben :</label></p>
                        <textarea class="textbody" style="width: 700px;height : 200px" maxlength="10000" name="comment" id="title-0" required></textarea>
                        <p><input type="submit" name="submit" value="Senden"></p>
                    </form>
                </div>
HEREDOC;
            echo $content_html;
            ?>
        </div>
    </div>
    <div class="row">
        <div class="twelve columns">
            <p class="padding_all"><a class="btn_default" href="index.php">Zurück zur Startseite</a></p>
        </div>
    </div>
</div>

<style>
    label {
        font-weight: bold;
        font-size: 15px;
    }

    .options-bar {
        margin-top: 1.0em;
        text-align: center;
    }

    .ui-autocomplete-category {
        font-weight: bold;
        padding: .2em .4em;
        margin: .8em 0 .2em;
        line-height: 1.5;
    }

    .error-message {
        padding: 20px;
        background-color: #f45f42;
        color: white;
    }

    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .info-message {
        padding: 20px;
        background-color: #34a3f0;
        color: white;
    }

    select {
        width: 200px;
    }
</style>
