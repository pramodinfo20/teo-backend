<?php
/**
 * mitarbeiter.php
 * Mitarbeitern Verwaltung
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <?php
            // 			echo $this->breadcrumb;
            ?>
        </div>
    </div>
    <?php
    if (is_array($this->msgs)) :?>
        <div class="row ">
            <div class="columns twelve">
                <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row ">
        <?php if (!$this->action && $this->user->user_can('newusers')) : ?>
            <h1>Was möchten Sie tun?</h1><br><br>
            <a href="?page=mitarbeiter&action=neu"><h2>1. Neues Mitarbeiter/in Konto erstellen.</h2></a><br>
            <br>
            <h2><a href="?page=mitarbeiter&action=aktuelle">2. Aktuelles Mitarbeiter/in Konto
                    bearbeiten.</a></h2><br>
            <br>
        <?php elseif ($this->action == 'neu' && $this->user->user_can('newusers')): ?>
            <div class="wizard" style="text-align: left">
                <?php echo $this->qform_new->getContent(); ?>
            </div>
        <?php elseif ($this->action == 'aktuelle'): ?>
            <div class="wizard" style="text-align: left">
                <?php echo $this->qform_exist->getContent(); ?>
            </div>

        <?php endif; ?>
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


    #usersSearchBox {
        width: 400px;
        padding: 10px 2px 10px 6px;
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
</style>
