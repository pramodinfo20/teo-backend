<?php
/**
 * FPDFCustomFooter.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle generating the Fahrzeugbegleitschein
 *
 */
class FPDFCustomFooter extends FPDF {
    protected $ikz;
    protected $vin;
    protected $version;
    protected $creator;
    protected $approver;

    function setFooterParams($ikz, $vin, $version = null, $creator = null, $approver = null) {
        $this->ikz = $ikz;
        $this->vin = $vin;
        $this->version = $version;
        $this->creator = $creator;
        $this->approver = $approver;

    }

    function Footer() {
        $this->SetY(-16);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 4, $this->ikz . ' / ' . $this->vin, 0, 0, 'R');
        $this->SetY(-10);
        if ($this->version)
            $this->Cell(30, 4, 'Version: ' . $this->version);
        if ($this->creator)
            $this->Cell(50, 4, 'Ersteller: ' . $this->creator);
        if ($this->approver)
            $this->Cell(50, 4, 'Freigabe : ' . $this->approver);
        $this->Cell(0, 4, date('d.n.Y'), 0, 0, 'R');

    }
}

?>