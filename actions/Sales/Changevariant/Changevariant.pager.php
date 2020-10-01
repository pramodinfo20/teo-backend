<!--  <div class="pager2">  -->
<table class="pagintable">
    <tbody>
    <tr>
        <td style="width:120px;">
            <?php

            if ($this->nameprefix == 'top') {
                echo '<input type="checkbox" id="id_select_all" onClick="SelectAllRows(' . "'vehicles_list_table'" . ', this.checked)"> &nbsp; <b>alle aus-/abwählen</b></br>';
                echo '(bisher <b><span id="id_numsel">' . $this->numsel . '</span></b> ausgewählt)';
            }
            ?>
        </td>
        <td class="pagination" style="width:500px;">
            <?php

            if ($this->numPages > 1) {
                $first_index = max(1, $this->S_currentPage - 5);
                $last_index = min($this->numPages, $first_index + 9);
                $prev_page = $this->S_currentPage - 1;
                $next_page = $this->S_currentPage + 1;

                if ($this->S_currentPage > 1)
                    echo "<span style=\"width:70px;\" class=\"used\"><a href=\"javascript: SubmitAndGoto ($prev_page)\" title=\"Zurück\">&laquo;&nbsp;Zurück</a>";
                else
                    echo "<span style=\"width:70px;\" class=\"unused\"><b>&nbsp;</b>";

                for ($index = $first_index; $index <= $last_index; $index++) {
                    if ($index == $this->S_currentPage)
                        echo "</span>\n<span class=\"active\"><b>$index</b>";
                    else
                        echo "</span>\n<span class=\"used\"><a href=\"javascript: SubmitAndGoto ($index)\" title=\"Seite $index\">$index</a>";
                }

                if ($this->S_currentPage < $this->numPages)
                    echo "</span><span class=\"used\" style=\"width:70px;\"><a href=\"javascript: SubmitAndGoto ($next_page)\" title=\"Weiter\">Weiter&nbsp;&raquo;</a>\n";
                else
                    echo "</span><span style=\"width:70px;\" class=\"unused\"><b>&nbsp;</b>\n";
                echo '</span>';
            }
            ?></td>
        <td style="width:200px;">
            <button type="submit"><img src="/images/symbols/icon-reset.png"></button>
            <?php
            echo '<select onChange="ChangePageSize(this)">';
            foreach ($this->availablePageSizes as $size) {
                echo "<option value=\"$size\"";
                if ($size == $this->S_numRowsPerPage) echo " selected";
                echo ">$size</option>\n";
            }
            echo '  </select>
        ';

            ?>Zeilen/pro Seite

        </td>

        <td style="width:250px;">
            <?php printf('<b>%d</b> Ergebnisse auf <b>%d</b> Seiten', $this->numResult, $this->numPages); ?>
        </td>
    </tr>
    </tbody>
</table>
<!-- </div>  -->
