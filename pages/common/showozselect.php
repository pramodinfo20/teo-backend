<?php
/**
 * assignvehiclevariant.php
 * Template for common function vehicle management
 * @author Pradeep Mohan
 */
?>

<div class="row ">
    <div class="columns twelve">

        <?php if ($this->qform_div) echo $this->qform_div->getContent(); ?>
        <?php if ($this->qform_zspl) echo $this->qform_zspl->getContent(); ?>
        <?php if ($this->qform_depot) echo $this->qform_depot->getContent(); ?>
        <br>Sie können auch direkt Teile des Namens oder der OZ-Nummer in das Feld zum Suchen eingeben.
        <?php
        if ($this->getaction == 'auszulieferende')
            echo '<br>Es werden nur ZSPLn/ZSPn mit unzugeordneten Ladepunkten angezeigt.';
        else
            echo '<br>Es werden nur ZSPLn/ZSPn mit Ladepunkten/StreetScooter Fahrzeugen angezeigt.';
        ?>
        <input type='hidden' name='action' class='action_get' value='<?php echo $this->getaction; ?>'>

        <?php //action get used by the javascript to add to the url that will be called upon selecting a div zsp or zspl?>
    </div>
</div>
