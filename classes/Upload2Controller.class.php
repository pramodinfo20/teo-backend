<?php

use \ForceUTF8\Encoding;

include('includes/class-CSV.php');
/**
 * controllerbase.class.php
 * The main class..
 * @author Pradeep Mohan
 */

/**
 * PageController Class, the main class
 */
class Upload2Controller extends PageController {
    protected $hrListForm;
    protected $uploadResultString;
    protected $storedHRList;
    protected $companyStructure;

    /**
     * @var NewQueryPgsql
     */
    public $oQueryHolder;

    function checkPerson($hash) {
        if (preg_match("/^([a-f0-9]{64})$/", $hash) == 1) {
            return true;
        } else {
            return false;
        }
    }

    function checkOrganization($organization) {
        if (strlen($organization) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $config_leitwarte = $GLOBALS['config']->get_db('leitwarte');
        $databasePtr = new DatabasePgsql (
            $config_leitwarte['host'],
            $config_leitwarte['port'],
            $config_leitwarte['db'],
            $config_leitwarte['user'],
            $config_leitwarte['password'],
            new DatabaseStructureCommon1()
        );
        $this->oDbPtr = $databasePtr;
        $this->oQueryHolder = new NewQueryPgsql($this->oDbPtr);

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        // company structure to get costcenter and name->id relation
        $this->companyStructure = $this->getCompanyStructure();

        // get last used id for upload iteration (to display last one and upload as n+1)
        $this->lastUploadIteration = $this->getLastUploadId();

        $this->displayHeader->printContent();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processUpload();
        } else {
            ;
        }

        // if empty - proper information showed in view
        $this->storedHRList = $this->getLastUploadList();

        $this->printContent();
    }

    function printContent() {
        include("pages/upload2.php");
    }

    private function getLastUploadId() {
        $maxUploadId = $this->ladeLeitWartePtr->newQuery('persons_list_hr')
            ->get("MAX(upload_id)")[0]['max'];
        if (is_null($maxUploadId))
            return 0;
        else
            return $maxUploadId;
    }

    public function getLastUploadList() {
        return $this->ladeLeitWartePtr->newQuery('persons_list_hr')
            ->where("upload_id", "=", $this->getLastUploadId())
            ->get("id, upload_id, person, organization_id, business_unit, kind, is_leader, deputy_organization_id");
    }

    public function processUpload() {
        $sDirToUpload = '/var/www/tmp/csv';
        $sUploadedFilePath = $_FILES['csvfile']['tmp_name'];
        $sUploadedFileName = $_FILES['csvfile']['name'];

        $sNewPathForUploadedFile = $sDirToUpload . '/' . time() . $sUploadedFileName;

        if (is_uploaded_file($sUploadedFilePath)) {
            if (move_uploaded_file($sUploadedFilePath, $sNewPathForUploadedFile)) {

                // prepare CSV with configuration
                $csvObject = new CSV();
                $csvObject->terminator = "\n";
                $csvObject->separator = ";";
                $csvObject->enclosed = '"';
                $csvObject->escaped = "\\";
                $csvObject->mimeType = "text/csv";

                $csvObject->readCSV($sNewPathForUploadedFile);

                $totalRows = $csvObject->totalRows();
                $allCsvRows = $csvObject->getAllRows();

                $costCenterList = array();

                // validation
                try {
                    // at least 2 rows
                    if ($totalRows < 2) {
                        throw new RuntimeException("File is too short! <2 rows found");
                    }

                    // header names
                    if ($allCsvRows[0][0] != "Bereich" || $allCsvRows[0][1] != "KST" || $allCsvRows[0][2] != "Beschreibung" ||
                        $allCsvRows[0][3] != "hash" || $allCsvRows[0][4] != "Status") {
                        throw new RuntimeException("Wrong header, `Bereich;KST;Beschreibung;hash;Status` expected");
                    }

                    // check each data row
                    foreach ($allCsvRows as $k => $aValue) {
                        if ($k == 0) continue; // header row
                        $k1 = $k + 1; // for printing
                        $businessUnit = $aValue[0];
                        $costCenter = $aValue[1];
                        $companyStructureName = Encoding::fixUTF8($aValue[2]);
                        $hashedName = $aValue[3];
                        $status = $aValue[4];

                        // empty field
                        if (empty($businessUnit))
                            throw new RuntimeException("No BusinessUnit given in line " . $k1);
                        if (empty($costCenter))
                            throw new RuntimeException("No CostCenter given in line " . $k1);
                        if (empty($companyStructureName))
                            throw new RuntimeException("No CompanyStructure given in line " . $k1);
                        if (empty($hashedName))
                            throw new RuntimeException("No HashedName given in line " . $k1);
                        if (empty($status))
                            throw new RuntimeException("No StatusKind given in line " . $k1);

                        // check if cost center already assigned and if yes - is it the same
                        if (array_key_exists($companyStructureName, $costCenterList)) {
                            if ($costCenterList[$companyStructureName] != $costCenter) {

                                throw new RuntimeException("Wrong CostCenter number in line " . $k1 . ", value " .
                                    $costCenterList[$companyStructureName] . " expected for " . $companyStructureName);
                            } else {
                                ;
                            }
                        } else {
                            $costCenterList[$companyStructureName] = $costCenter;
                        }

                        // check if hashname is ok
                        if (preg_match("/^([a-f0-9]{64})$/", $hashedName) != 1) {
                            throw new RuntimeException("Wrong hashed name in line " . $k1);
                        }

                        // get CS id, create new entry if necessary
                        $organizationId = $this->getStructureIdByName($companyStructureName);
                        if ($organizationId == false) {
                            // prepare new structure
                            $company = $this->addCompanyStructureByName($companyStructureName, $costCenter);

                            if (!$company)
                                throw new RuntimeException("Cannot add new company: " . $companyStructureName);

                            // REFRESH structure to get costcenter and name->id relation
                            $this->companyStructure = $this->getCompanyStructure();

                            // it is added by now
                            $organizationId = $this->getStructureIdByName($companyStructureName);

                            if ($organizationId == 0)
                                throw new RuntimeException("Cannot find new Company Structure: " . $companyStructureName);

                            // notify user that company structure was missing and has been added
                            $this->uploadResultString .= '<div class="info-message">New CompanyStructure <strong>'
                                . $companyStructureName . '</strong> has been added</div><br/>';
                        }

                        // error when cannot add new organization
                        if ($organizationId == 0) {
                            throw new RuntimeException("Cannot add new Company Structure: " . $companyStructureName);
                        }
                    }

                    // more validations

                    // store list to database only if no exception
                    $this->storeHRList($allCsvRows);
                    $this->uploadResultString .= '<div class="success-message">We have processed ' .
                        count($allCsvRows) . ' records from Uploaded CSV file: <strong>'
                        . $sUploadedFileName . '</strong></div>';

                } catch (RuntimeException $e) {
                    $this->uploadResultString = '<div class="error-message">' . $e->getMessage() . '</div>';
                }
            }
        } else {
            $this->uploadResultString = '<div class="error-message">Possible file upload attack!</div>';
        }

    }

    function storeHRList($csvAllData) {
        // incoming format: " Bereich;KST;Beschreibung;hash;Status "
        $newUploadId = $this->getLastUploadId() + 1;
        $vals = array();
        foreach ($csvAllData as $k => $aValue) {
            if ($k == 0) continue; // header row
            $businessUnit = $aValue[0];
            $organizationId = $this->getStructureIdByName(Encoding::fixUTF8($aValue[2])); // from db!
            $hashedName = $aValue[3];
            $status = $aValue[4];
//      echo '<pre>' . " ('$newUploadId', '$hashedName', '$organizationId', '$businessUnit', '$status', false, false )" .  '</pre>';
            array_push($vals, " ($newUploadId, '$hashedName', $organizationId, '$businessUnit', '$status', false, 0 )");
        }

        $insertColumns = "upload_id, person, organization_id, business_unit, kind, is_leader, deputy_organization_id";
        $insertValues = implode(',', $vals);
        $insertQuery = "INSERT INTO persons_list_hr (" . $insertColumns . ") VALUES " . $insertValues;
//    echo($insertQuery);
        $this->oQueryHolder->query($insertQuery);
    }

    function addCompanyStructureByName($n, $costcenter) {
        $q = "INSERT INTO sts_organization_structure (name, parent_id, costcenter) VALUES ('$n', 0, $costcenter) RETURNING id, name, costcenter";
        return pg_fetch_row($this->oQueryHolder->query($q));
    }

    function getCompanyStructure() {
        return $this->ladeLeitWartePtr->newQuery('sts_organization_structure')
            ->get("name, id, costcenter");
    }

    function getStructureIdByName($name) {
        $key = array_search($name, array_column($this->companyStructure, 'name'));
        if ($key === false)
            return false;
        else
            return $this->getCompanyStructure()[$key]['id'];
    }

    function getStructureCostCenterByName($name) {
        $key = array_search($name, array_column($this->companyStructure, 'name'));
        if ($key === false)
            return false;
        else
            return $this->getCompanyStructure()[$key]['costcenter'];
    }

    function checkFile($file) {

//    $totalRows = $file->totalRows();
//      for ($row = 1; $row < $totalRows; $row++) {
//
//        $col = 0;
//
//        $person = $file->getRowCol($row, $col);
//        $organization = $file->getRowCol($row, ++$col);
//        $organizationID = (int)$file->getRowCol($row, ++$col);
//        $parentID = (int)$file->getRowCol($row, ++$col);
//        $isLeader = (bool)$file->getRowCol($row, ++$col);
//        $isDeputy = (bool)$file->getRowCol($row, ++$col);
//
//        if ($this->checkOrganization($person)) { //TODO: check person function to be fixed
//          if ($this->checkOrganization($organization)) {
//            if (intval($organizationID) >= 0) {
//              if (intval($parentID) >= 0) {
//                if (is_bool($isLeader)) {
//                  if (is_bool($isDeputy)) {
//                    return true;
//                  } else {
//                    echo "Please check file for empty values in Deputy or correct format";
//                    return false;
//                  }
//                } else {
//                  echo "Please check file for empty values in Leader or correct format";
//                  return false;
//                }
//              } else {
//                echo "Please check file for empty values in Parent ID or correct format";
//                return false;
//              }
//            } else {
//              echo "Please check file for empty values in Organization ID or correct format";
//              return false;
//            }
//          } else {
//            echo "Please check file for empty values in Organization or correct format";
//            return false;
//          }
//        } else {
//          echo "Please check file for empty values in Person or correct format";
//          return false;
//        }
//      }
    }
}

function recursive_array_search($needle, $haystack) {
    foreach ($haystack as $key => $value) {
        $current_key = $key;
        if ($needle === $value OR (is_array($value) && recursive_array_search($needle, $value) !== false)) {
            return $current_key;
        }
    }
    return false;
}