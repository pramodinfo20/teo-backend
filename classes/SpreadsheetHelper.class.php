<?php

/**
 * SpreadsheetHelper.class.php
 *
 * @author Pradeep Mohan
 */
require $_SERVER['STS_ROOT'] . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class to handle PHP Spreadsheet functions
 */
class SpreadsheetHelper {
    function __construct() {

    }


    function CreateSpreadsheet() {
        // Create new Spreadsheet object
        return new Spreadsheet();
    }

    function CreateWriter($spreadsheet, $format) {
        return IOFactory::createWriter($spreadsheet, ucfirst($format));
    }

    function appendExport($export_data, $fname, $format) {
        $reader = IOFactory::createReader(ucfirst($format));
        $spreadsheet = $reader->load("/tmp/" . $fname . '.' . $format);
        $lastrow = 0;
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $lastrow = $row->getRowIndex();
            }
        }
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $export_data,  // The data to set
                NULL,        // Array values with this value will not be set
                'A' . ++$lastrow         // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
            );
        $writer = IOFactory::createWriter($spreadsheet, ucfirst($format));
        //         $writer->save('php://output');
        $writer->save("/tmp/" . $fname . '.' . $format);
    }

    function generateExport($export_data, $fname, $format) {
        $spreadsheet = $this->CreateSpreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Sts')
            ->setLastModifiedBy('Sts')
            ->setTitle('TEO Daten')
            ->setSubject('TEO Daten')
            ->setDescription('TEO Daten');

        $spreadsheet->getActiveSheet()
            ->fromArray(
                $export_data,  // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
            );

        foreach (range('A', $spreadsheet->getActiveSheet()->getHighestDataColumn()) as $col) {
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

//         foreach (range('A','E') as $col) {
//               $activeSheet->getColumnDimension($col)->setAutoSize(true); 
//         }
//             $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
//         $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
//         $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
//         $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
//         // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('TEO Daten');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);


//         // Redirect output to a client’s web browser (Xlsx)
//         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//         header('Content-Disposition: attachment;filename="01simple.xlsx"');
//         header('Cache-Control: max-age=0');
//         // If you're serving to IE 9, then the following may be needed
//         header('Cache-Control: max-age=1');

//         // If you're serving to IE over SSL, then the following may be needed
//         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//         header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
//         header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//         header('Pragma: public'); // HTTP/1.0

        $writer = $this->CreateWriter($spreadsheet, $format);
//         $writer->save('php://output');
        $writer->save("/tmp/" . $fname . '.' . $format);
//         exit;
    }

    function createReaderFromFile($filepath) {
        $inputFileType = IOFactory::identify($filepath);
        /**  Create a new Reader of the type that has been identified  **/
        $reader = IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a Spreadsheet Object  **/
        return $reader;
    }
}
