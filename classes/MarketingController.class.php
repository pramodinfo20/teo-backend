<?php
/**
 * MarketingController.class.php
 * Controller for User Role Zentrale
 * @author Pradeep Mohan
 */


class MarketingController extends PageController {
    protected $content;
    protected $msgs;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->content = "";
        $this->table_orders = array();
        $this->msgs = "";
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        $this->action = $this->requestPtr->getProperty('action');
        if (isset($this->action))
            call_user_func(array($this, $this->action));


        $this->displayHeader->printContent();

        $this->printContent();

    }

    function listcsv() {
        $this->qform_dritt = new QuickformHelper ($this->displayHeader, "drittkunden_orders_form");
        $this->qform_dritt->csvUpload("Drittkunden Aufträge", "drittkunden_orders");
    }

    function listorders() {
        $third_party_orders = $this->ladeLeitWartePtr->thirdpartyOrdersPtr->newQuery()->get('order_num,delivery_date,vehicle_variant_label,vehicle_color,depot_id,pr_contact,pr_tel,penta_folge_id,vehicle_delivered');
        $processed_orders = array();
        foreach ($third_party_orders as $order) {
            $depot = $this->ladeLeitWartePtr->depotsPtr->newQuery()->where('depot_id', '=', $order['depot_id'])->getOne('*');
            $processed_orders[] = array($order['order_num'],
                $order['delivery_date'],
                $order['vehicle_variant_label'],
                $order['vehicle_color'],
                str_replace('Dummy ZSP', '', $depot['name']),
                $depot['street'],
                $depot['housenr'],
                $depot['postcode'],
                $depot['place'],
                $order['pr_contact'],
                $order['pr_tel'],
                $order['vehicle_delivered']);
        }

        $headings = array();
        $headings[]['headingone'] = explode(',', 'Auftragsnummer,Wunschtermin,Fahrzeugvariante,Fahrzeug Farbe,accountname,Straße (Rechnungsanschrift),Hausnummer,PLZ (Rechnungsanschrift),Stadt (Rechnungsanschrift),Primärer Kontakt,Kontakt Telefon,Fahrzeug Ausgeliefert?');
        $this->table_orders = new DisplayTable (array_merge($headings, $processed_orders));
    }

    function save_drittkunden_orders_upload() {
        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }
        ini_set("auto_detect_line_endings", true);

        $filestr = file_get_contents($filename);

        $handle = FALSE;

        if (!mb_detect_encoding($filestr, 'UTF-8', true)) {
            if (mb_detect_encoding($filestr, 'ISO-8859-15', true)) {
                $fc = iconv('ISO-8859-15', 'utf-8', $filestr);
                $handle = fopen("php://memory", "rw");
                fwrite($handle, $fc);
                fseek($handle, 0);
            }

        } else
            $handle = fopen($filename, "r");


        if ($handle !== FALSE) {

            if (substr_count(fgets($handle), ';') > 0) $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') > 0) $this->delimiter = ',';
            else $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';

            rewind($handle);

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {
                $datastr = implode(',', $data);

                //$str = mb_convert_encoding($str, "UTF-7", "EUC-JP");


                if (!preg_match('/^\d+$/', $data[0])) continue;

                //Auftragsnummer
                $order_num = trim($data[0]);
                //Wunschtermin
                $delivery_date = trim($data[1]);
                //Fahrzeugvariante
                $vehicle_variant_label = trim($data[2]);
                //Fahrzeugfarbe
                $vehicle_color = trim($data[3]);
                //accountname
                $depot = trim($data[4]);
                //Straße (Rechnungsanschrift)
                $street = trim($data[5]);
                //Hausnummer (Rechnungsanschrift)
                $housenr = trim($data[6]);
                //PLZ (Rechnungsanschrift)
                $postcode = trim($data[7]);
                //Stadt (Rechnungsanschrift)
                $place = trim($data[8]);
                //Primärer Kontakt
                $pr_contact = trim($data[9]);
                //Kontakt Telefon
                $pr_tel = trim($data[10]);


                //check if third party depot exists and add the order
                $processed_order['order_num'] = $order_num;
                $processed_order['delivery_date'] = date('Y-m-d', strtotime($delivery_date));
                $processed_order['vehicle_variant_label'] = $vehicle_variant_label;
                $processed_order['vehicle_color'] = $vehicle_color;
                $processed_order['pr_contact'] = $pr_contact;
                $processed_order['pr_tel'] = $pr_tel;

                $depot_id = $this->ladeLeitWartePtr->depotsPtr->getThirdPartyDepot($depot, $street, $housenr, $postcode, $place);

                if ($depot_id === false) {
                    $new_depot['name'] = $depot;
                    $new_depot['street'] = $street;
                    $new_depot['housenr'] = $housenr;
                    $new_depot['postcode'] = $postcode;
                    $new_depot['place'] = $place;
                    //add new depot with depot_restriction_id=NULL
                    $processed_order['depot_id'] = $this->ladeLeitWartePtr->depotsPtr->insertThirdPartyDepot($new_depot);
                } else {
                    $processed_order['depot_id'] = $depot_id;
                }

                if ($this->ladeLeitWartePtr->thirdpartyOrdersPtr->orderExists($order_num)) {
                    unset($processed_order['order_num']);
                    $this->ladeLeitWartePtr->thirdpartyOrdersPtr->newQuery()->where('order_num', '=', $order_num)->update(array_keys($processed_order), array_values($processed_order));

                } else
                    $this->ladeLeitWartePtr->thirdpartyOrdersPtr->newQuery()->insert($processed_order);

            }
        }

        $this->action = 'listcsv';
        $this->msgs[] = 'CSV Datei erfolgreich hochgeladen!';
        $this->listcsv();
    }

    function printContent() {
        include("pages/" . $this->user->getUserRole() . ".php");
    }
}

