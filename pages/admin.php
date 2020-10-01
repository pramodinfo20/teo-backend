<?php
/**
 * admin.php
 * Template für die Benutzer Rolle Admin
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">

                <li>
                    <a href="index.php"
                       class="sts_submenu <?php if (!isset($this->action)) echo 'selected'; ?>">Home</a>
                </li>
                <li>
                    <a href="?action=duplicatedepots" data-target="?action=duplicatedepots"
                       class="sts_submenu <?php if ($this->action == "?action=duplicatedepots") echo 'selected'; ?>">ZSPn
                        prüfen</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
    if (is_array($this->msgs)): ?>
        <div class="row ">
            <div class="columns twelve">
		<span class="error_msg">
			<?php echo implode('<br>', $this->msgs); ?>
		</span>
            </div>
        </div>
    <?php endif; ?>
    <?php
    if (isset($this->action) && $this->action == "duplicatedepots") : ?>


        <h1>Doppelte ZSPn</h1>
        <?php

        foreach ($this->trees as $tree) {
            if (sizeof($tree) == 2) $cols = 'six';
            else $cols = 'four';

            echo '<div class="row ">';
            foreach ($tree as $depot_tree) {
                echo '<div class="columns ' . $cols . '" >';
                echo $depot_tree;
                echo '</div>';
            }
            echo '</div>
						<div class="row ">
						<div class="columns twelve "><hr>
						</div>
						</div>';

        }
    endif;
    ?>
</div>		
