<?php
/**
 * FPDFFooterJS.class.php
 * http://www.fpdf.org/en/script/script50.php
 * http://www.fpdf.org/en/script/script36.php
 * http://www.fpdf.org/en/script/script3.php
 * @author Pradeep Mohan
 */

/**
 * Class to handle converting HTML tables to PDF and allow adding javscsript to the PDF so that the print dialog opens automatically
 *
 */

if (file_exists("/var/www/WebinterfaceNew/fpdf/fpdf.php"))
    require_once('/var/www/WebinterfaceNew/fpdf/fpdf.php');

class FPDFFooterJS extends FPDF {
    protected $javascript;
    protected $n_js;

    protected $version;
    protected $creator;
    protected $approver;


    //function hex2dec
    //returns an associative array (keys: R,G,B) from a hex html code (e.g. #3FE5AA)
    function hex2dec($hexcolor = "#000000") {
        $R = substr($hexcolor, 1, 2);
        $R = hexdec($R);
        $G = substr($hexcolor, 3, 2);
        $G = hexdec($G);
        $B = substr($hexcolor, 5, 2);
        $B = hexdec($B);
        return $R . ',' . $G . ',' . $B;
    }


    function IncludeJS($script, $isUTF8 = false) {
        if (!$isUTF8)
            $script = utf8_encode($script);
        $this->javascript = $script;
    }

    function _putjavascript() {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_put('<<');
        $this->_put('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_put('>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/S /JavaScript');
        $this->_put('/JS ' . $this->_textstring($this->javascript));
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_put('/Names <</JavaScript ' . ($this->n_js) . ' 0 R>>');
        }
    }

    //conversion pixel -> millimeter in 72 dpi
    function px2mm($px) {
        return (str_replace('px', '', $px) * 25.4 / 72);
    }


    function SetWidths($w) {
        foreach ($w as &$width) {
            if (strpos($width, 'px') !== false)
                $width = $this->px2mm($width);
        }
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a) {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data) {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }


    function setFooterParams($version = null, $creator = null, $approver = null) {
        $this->version = $version;
        $this->creator = $creator;
        $this->approver = $approver;

    }

    function Footer() {
        $this->SetFont('Arial', '', 10);
        $this->SetY(-10);
        if ($this->version) $this->Cell(30, 4, 'Version: ' . $this->version);
        if ($this->creator) $this->Cell(50, 4, 'Ersteller: ' . $this->creator);
        if ($this->approver) $this->Cell(50, 4, 'Freigabe : ' . $this->approver);

    }

}

?>