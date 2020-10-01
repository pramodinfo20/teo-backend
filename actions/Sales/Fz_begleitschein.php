<?php

class ACtion_Fz_begleitschein extends AClass_Base
{

    protected $sales_protokoll = null;

    protected $ladeLeitWarte = null;


    function __construct(PageController $pageController)
    {

        parent::__construct($pageController);

        $this->ladeLeitWarte = $this->controller->GetObject("ladeLeitWarte");
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");

        if (! file_exists('/tmp/gen_pdf'))
            mkdir('/tmp/gen_pdf');

    }


    function Execute()
    {

        $this->mode = $_REQUEST['mode'];
        $vehicle_ids = explode(',', $_REQUEST['vlist']);

        $vehicles = $this->vehiclesPtr->newQuery()
            ->where("vehicle_id", "IN", $vehicle_ids)
            ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
            ->orderBy('vehicle_id', 'ASC')
            ->get('vehicle_id,vin,code,ikz,colors.name as colorname');

        $this->sales_protokoll = new SalesProtokoll($vehicles, $this->ladeLeitWarte);

    }


    function WriteHtmlPage($displayheader, $displayfooter)
    {

        readfile($this->sales_protokoll->getPdfName());

    }

}
?>