<?php
?>
<div class="inner_container">
    <div class="row ">
        <div class="columns six">
            <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
        </div>
    </div>

    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">
                <li>
                    <a href="index.php"
                       class="sts_submenu <?php if (!isset($this->action)) echo 'selected'; ?>">Home</a>
                </li>
                <li>
                    <a href="?action=fpsMail" data-target="main"
                       class="sts_submenu <?php if ($this->action == "fpsMail") echo 'selected'; ?>">Mail an FPS</a>
                </li>
                <li>
                    <a href="?action=assignStations" data-target="main"
                       class="sts_submenu <?php if ($this->action == "assignStations") echo 'selected'; ?>">Automatisch
                        Ladepunkten zuweisen</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
            <?php echo $this->content; ?>
        </div>
    </div>
    <?php if (isset($this->debugcontent)) : ?>
        <div class="row ">
            <div class="columns twelve">
                <?php echo $this->debugcontent; ?>
            </div>
        </div>
    <?php endif; ?>
</div>