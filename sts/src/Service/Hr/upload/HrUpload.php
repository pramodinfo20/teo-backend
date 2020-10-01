<?php

namespace App\Service\Hr\upload;

use App\Entity\HistoryPersonsListHr;
use App\Entity\PersonsListHr;
use App\Entity\StsOrganizationStructure;
use App\Entity\Users;
use App\Service\AbstractService;
use App\Service\Hr\upload\UploadResults;
use App\Utils\CSV;
use DateTime;
use RuntimeException;
use Exception;
use ForceUTF8\Encoding;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HrUpload extends AbstractService
{
    const UPLOAD_CSV_DIR = '/var/www/tmp/csv';

    public function processUpload(UploadedFile $csvFile): UploadResults
    {
        $errors = [];
        $success = [];
        $information = [];
        $companyStructure = $this->getCompanyStructure();

        $sUploadedFileName = $csvFile->getClientOriginalName();

        $sNewPathForUploadedFile = self::UPLOAD_CSV_DIR . DIRECTORY_SEPARATOR . time() . $sUploadedFileName;

        if ($csvFile->getError() == UPLOAD_ERR_OK) {
            try {
                $csvFile->move(self::UPLOAD_CSV_DIR, time() . $sUploadedFileName);

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

                if (is_string(reset($allCsvRows))) {
                    $allCsvRows = array_map(function ($row) {
                        return explode(",", $row);
                    }, $allCsvRows);
                }

                $costCenterList = array();

                // validation
                try {
                    // at least 2 rows
                    if ($totalRows < 2) {
                        throw new RuntimeException("File is too short! <2 rows found");
                    }

                    // header names
                    if ($allCsvRows[0][0] !== 'Bereich' ||
                        $allCsvRows[0][1] !== 'KST' ||
                        $allCsvRows[0][2] !== 'Beschreibung' ||
                        $allCsvRows[0][3] !== 'hash' ||
                        $allCsvRows[0][4] !== 'Status' ||
                        $allCsvRows[0][5] !== 'Leader' ||
                        $allCsvRows[0][6] !== 'Deputy_organization') {
                        throw new RuntimeException('Wrong header, `Bereich;KST;Beschreibung;hash;Status` expected');
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
                        $isLeader = $aValue[5];
                        $deputyOrganization = $aValue[6];


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
                        if (empty($isLeader))
                            throw new RuntimeException('No leader given in line ' . $k1);
//                        if (empty($deputyOrganization))
//                            throw new RuntimeException('No deputy organization given in line ' . $k1);


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
                        $organization = $this->getStructureByName($companyStructure, $companyStructureName);
                        if ($organization == false) {
                            try {
                                // prepare new structure
                                $company = new StsOrganizationStructure();
                                $company->setCostcenter($costCenter)
                                    ->setName($companyStructureName)
                                    ->setParent(null);
                                $this->manager->persist($company);
                                $this->manager->flush();
                            } catch (Exception $ex) {
                                throw new RuntimeException("Cannot add new company: " . $companyStructureName);
                            }

                            // REFRESH structure to get costcenter and name->id relation
                            $companyStructure = $this->getCompanyStructure();

                            // it is added by now
                            $organization = $this->getStructureByName($companyStructure, $companyStructureName);

                            if ($organization == false)
                                throw new RuntimeException("Cannot find new Company Structure: " . $companyStructureName);

                            // notify user that company structure was missing and has been added
                            $information[] = 'New CompanyStructure has been added.';
                        }

                        // error when cannot add new organization
                        if ($organization == false) {
                            throw new RuntimeException("Cannot add new Company Structure: " . $companyStructureName);
                        }
                    }

                    // more validations

                    // store list to database only if no exception
                    $this->storeHRList($allCsvRows);
                    $success[] = 'We have processed ' . count($allCsvRows) . ' records from Uploaded CSV file.';

                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            } catch (FileException $ex) {
                $errors[] = 'Possible file upload attack!';
            }
        }

        return new UploadResults($errors, $success, $information);
    }

    private function getCompanyStructure()
    {
        return $this->manager->getRepository(StsOrganizationStructure::class)->findAll();
    }

    private function getStructureByName($companyStructure, $name)
    {
        $structure = array_filter($companyStructure, function ($structure) use ($name) {
            return $name == $structure->getName();
        });


        if (empty($structure)) {
            return false;
        } else {
            return reset($structure);
        }
    }

    private function storeHRList($csvAllData): void
    {
        // incoming format: " Bereich;KST;Beschreibung;hash;Status "
        $newUpload = new HistoryPersonsListHr();
        $newUpload->setCreatedAt(new DateTime(date("Y-m-d H:i:s")))
            ->setCreatedBy($this->manager->getRepository(Users::class)->findOneBy
            (['id' => $_SESSION['sts_userid']]));

        $this->manager->persist($newUpload);

        $companyStructure = $this->getCompanyStructure();

        foreach ($csvAllData as $k => $aValue) {
            if ($k == 0) continue; // header row
            $businessUnit = $aValue[0];
            $organization = $this->getStructureByName($companyStructure, Encoding::fixUTF8($aValue[2])); // from
            // db!
            $hashedName = $aValue[3];
            $status = $aValue[4];
            $isLeader = $aValue[5];
            $deputyOrganizationId = null;
            if (!empty($aValue[6])) {
                $deputyOrganizationId = $this->getStructureByName($companyStructure, Encoding::fixUTF8($aValue[6]));
            }
            $personListHr = new PersonsListHr();
            $personListHr->setUpload($newUpload)
                ->setPerson($hashedName)
                ->setOrganization($organization)
                ->setBusinessUnit($businessUnit)
                ->setKind($status)
                ->setIsLeader($isLeader)
                ->setDeputyOrganization($deputyOrganizationId);
            $this->manager->persist($personListHr);
        }
        $this->manager->flush();

        $hr = $this->manager->getRepository(HistoryPersonsListHr::class)->find($newUpload);
        $hr->setCreatedAt(new DateTime(date("Y-m-d H:i:s")));
        $this->manager->persist($hr);

        $this->manager->flush();
    }
}