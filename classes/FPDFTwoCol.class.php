<?php
/**
 * FPDFTwoCol.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle generating the Fahrzeugbegleitschein
 *
 */
class FPDFTwoCol extends FPDF {
    var $col = 0;
    var $nextColY = 10;
    var $colWidth = 65;

    function setColWidth($colWidth) {
        $this->colWidth = $colWidth;
    }

    function setNextColY($col) {
        $this->nextColY = ($col);
    }

    function SetCol($col) {
        // Move position to a column
        $this->col = $col;
        $x = 10 + $col * $this->colWidth;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }

    function AcceptPageBreak() {
        if ($this->col < 2) {
            // Go to next column
            $this->SetCol($this->col + 1);
            $this->SetY($this->nextColY);
            return false;
        } else {
            // Go back to first column and issue page break
            $this->SetCol(0);
            return true;
        }
    }
}

?>