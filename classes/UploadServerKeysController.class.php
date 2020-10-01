<?php

class UploadServerKeysController extends SymfonyBaseController {

    public function __construct($ladeLeitWartePtr, $container, $requestPtr) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, 'admin/keys',
            [],
            []);
        /*Parameters to avoid */
        //parent::setParametersToAvoid();
        /*Parameters without keys */
        //parent::setParametersWithoutKeys();


        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        }
    }

    /*
     * Do not write your own methods. Instead of this use:
     * regenerateView - print new View
     * ajaxCall - to make GET ajax
     * ajaxCallPost - to make POST ajax
     * ajaxCallDelete - to make DELETE ajax
     *
     * If you want to change the behavior of generating routing from arguments add parameters to these arrays:
     * parametersToAvoid - skip parameter
     * parametersWithoutKeys - get only value, without key ex. /value instead of /key/value
     *
     */
}
//    public $oQueryHolder;
//
//    public $sCreateDbTable = "";
//
//    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
//        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
//        $this->container = $container;
//        $this->requestPtr = $requestPtr;
//        $this->user = $user;
//        $config_leitwarte = $GLOBALS['config']->get_db('leitwarte');
//        $databasePtr = new DatabasePgsql (
//            $config_leitwarte['host'],
//            $config_leitwarte['port'],
//            $config_leitwarte['db'],
//            $config_leitwarte['user'],
//            $config_leitwarte['password'],
//            new DatabaseStructureCommon1()
//        );
//        $this->oDbPtr = $databasePtr;
//        $this->oQueryHolder = new NewQueryPgsql($this->oDbPtr);
//        $this->msgs = null;
//        $this->action = $this->requestPtr->getProperty('action');
//
//        $this->displayHeader = $this->container->getDisplayHeader();
//        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
//
//        if (isset($_GET['method'])) {
//            $method = $_GET['method'];
//            $this->$method();
//        } else {
//            $this->printContent();
//        }
//    }
//
//    function printContent() {
//        $this->displayHeader->printContent();
//        include("pages/uploadServerKeys.php");
//    }
//
//    function checkIfKeysExist() {
//        return $this->ladeLeitWartePtr->newQuery("server_keys")->get("id") == null ? false : true;
//    }
//
//    function uploadKeys() {
//        if (isset($_POST["uploadKeys"]))
//            if (isset($_FILES['privateKey']) && isset($_FILES['publicKey'])) {
//                putenv("GNUPGHOME=/tmp");
//
//                $gpg0 = new gnupg();
//                $gpg1 = new gnupg();
//                if ($gpg0->import(file_get_contents($_FILES['privateKey']['tmp_name'])) != false && $gpg1->import(file_get_contents($_FILES['publicKey']['tmp_name'])) != false) {
//                    $this->ladeLeitWartePtr->changeTableToInsert("server_keys")->addMultiple(['private_key', 'public_key', 'private_size', 'public_size'], [[file_get_contents($_FILES['privateKey']['tmp_name']), file_get_contents($_FILES['publicKey']['tmp_name']), filesize($_FILES['privateKey']['tmp_name']), filesize($_FILES['publicKey']['tmp_name'])]]);
//                    echo '<div id="upload-success" title="Upload Success">
//                    <p>Now users can make new signatures.</p>
//                    </div>';
//
//                    echo '<script>$( function() {
//                                       $("#upload-success").dialog({
//                                                  autoOpen: true,
//                                                  resizable: false,
//                                                  height: "auto",
//                                                  width: "auto",
//                                                  modal: true,
//                                                  buttons: {
//                                                      "Close": function () {
//                                                          $(this).dialog("close");
//                                                      }
//                                                  }
//                                              })
//                                       });
//                  </script>';
//                } else {
//                    echo '<div id="upload-failure0" title="Upload Failure">
//                    <p>Wrong key format.</p>
//                    </div>';
//
//                    echo '<script>$( function() {
//                                       $("#upload-failure0").dialog({
//                                                  autoOpen: true,
//                                                  resizable: false,
//                                                  height: "auto",
//                                                  width: "auto",
//                                                  modal: true,
//                                                  buttons: {
//                                                      "Close": function () {
//                                                          $(this).dialog("close");
//                                                      }
//                                                  }
//                                              })
//                                       });
//                  </script>';
//                }
//            } else {
//                echo '<div id="upload-failure" title="Upload Failure">
//                    <p>Missing file.</p>
//                    </div>';
//
//                echo '<script>$( function() {
//                                       $("#upload-failure").dialog({
//                                                  autoOpen: true,
//                                                  resizable: false,
//                                                  height: "auto",
//                                                  width: "auto",
//                                                  modal: true,
//                                                  buttons: {
//                                                      "Close": function () {
//                                                          $(this).dialog("close");
//                                                      }
//                                                  }
//                                              })
//                                       });
//                  </script>';
//            }
//    }