<?php
?>
<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">
                <li>
                    <a href="?action=firstaction" data-target="firstaction"
                       class="sts_submenu <?php if ($this->action == "firstaction") echo 'selected'; ?>">FirstAction</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row ">
        <div class="columns six">
            <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
        </div>
    </div>

    <div id="firstaction" class="submenu_target_child current row">
        <div class="columns twelve ">
            <h1>First Action</h1>
            <?php
            if (isset($this->content)) echo $this->content;
            ?>
        </div>
    </div>
</div>