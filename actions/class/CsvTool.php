<?php
require_once ($_SERVER['STS_ROOT'] . "/includes/sts-defines.php");

class AClass_CsvTool extends AClass_Base
{


    function __construct($postName, $bForUpdate)
    {

        parent::__construct();

        $this->S_root = &InitSessionVar($this->S_actionVar['CsvTool'], array());
        $this->S_csvColumns = &InitSessionVar($this->S_root['Columns'], false);
        $this->S_csvColMap = &InitSessionVar($this->S_root['ColMap'], false);
        $this->S_identCol = &InitSessionVar($this->S_root['identCol'], false);
        $this->S_identIndex = &InitSessionVar($this->S_root['identIndex'], false);
        $this->S_UploadedFile = &InitSessionVar($this->S_root['UploadedFile'], false);

        $this->postName = $postName;
        $this->forUpdate = $bForUpdate;
        $this->debug_csv = false;
        $this->OutputBuffer = false;
        $this->headlines = $this->DefaultHeadlines();
        $this->selectableCols = array_flip($this->headlines);
        $this->CvsDataRegex = $this->DefaultDataRegex();
        $this->step = 0;
        $this->CsvResult = 0;
        $this->delimiter = false;

    }


    // ==============================================================================================
    function CleanUp()
    {

        unset($this->S_actionVar['CsvTool']);

    }


    // ==============================================================================================
    function DefaultHeadlines()
    {

        return [];

    }


    function DefaultDataRegex()
    {

        return [];

    }


    function removeCols($cols)
    {

        $cols = explode(',', str_replace(' ', '', strtolower($cols)));
        foreach ($cols as $cn) {
            foreach ($this->headlines as $hdln => $c) {
                if ($c == $cn)
                    unset($this->headlines[$hdln]);
            }
        }
        unset($this->CvsDataRegex[$cn]);
        $this->selectableCols = array_flip($this->headlines);

    }


    /*
     * function AddHeadlines ($col_id, $healine_variations)
     * {
     * foreach ($healine_variations as $headline)
     * {
     * $this->headlines[$headline] = $col_id;
     * }
     * }
     *
     * // ==============================================================================================
     *
     * function SetHeadlines ($hdlDef)
     * {
     * $this->headlines = $hdlDef;
     * }
     */
    // ==============================================================================================
    function ScanColumDefFromHeadline($cells)
    {

        $headlines_found = [];
        $num_found = 0;

        if ($this->debug_file)
            fprintf($this->debug_file, "Scan Columdef from Headline (%s)\n", implode(',', $cells));

        foreach ($cells as $pos => $upCell) {
            $loCell = strtolower(trim($upCell));
            $best_result = 0.;
            $best_col = "";

            foreach ($this->headlines as $hdln => $col) {
                $hdln = strtolower($hdln);
                similar_text($loCell, $hdln, $percent);

                if (($percent >= 80.) && ($percent > $best_result)) {
                    $best_result = $percent;
                    $best_col = $col;
                    $ergb = sprintf("ok: %.2f", $percent);
                } else {
                    $ergb = sprintf("faild: %.2f", $percent);
                }

                if ($this->debug_file)
                    fprintf($this->debug_file, "%10s|%20s|%s\n", $ergb, $loCell, $hdln);
            }

            if ($best_col) {
                $headlines_found[$pos] = $best_col;
                $num_found ++;
            }
        }

        if ($num_found > 0) {
            if ($this->debug_file) {
                $strColdef = "";
                for ($pos = 0; $pos < count($cells); $pos ++) {
                    $strColdef .= " | " . safe_val($headlines_found, $pos, "-");
                }
                fprintf($this->debug_file, "COLUMNS: %s\n\n", substr($strColdef, 3));
            }
            return $headlines_found;
        }

        return false;

    }


    // ==============================================================================================
    function ScanColumDefFromData($cells, $specific_data_maps)
    {

        $headlines_found = [];
        $num_found = 0;

        if ($this->debug_file)
            fprintf($this->debug_file, "Scan Columdef from Data (%s)\n", implode(',', $cells));

        foreach ($cells as $pos => $cell) {
            $upCell = strtoupper(trim($cell));

            if ($specific_data_maps) {
                foreach ($specific_data_maps as $col => $map) {
                    if ($map[$upCell]) {}
                }
            }
            if ($wc_map[$upCell]) {
                $headlines_found[$pos] = 'vehicle_variants';
                $num_found ++;
            } else {
                foreach ($this->CvsDataRegex as $col => $regex) {
                    if (preg_match("/^$regex\$/", $upCell)) {
                        $headlines_found[$pos] = $col;
                        $num_found ++;
                        $ergb = "ok";
                        break;
                    } else {
                        $ergb = "faild";
                    }

                    if ($this->debug_file)
                        fprintf($this->debug_file, "%10s|%20s|%s\n", $ergb, $upCell, $regex);
                }
            }
        }

        if ($num_found > 0) {
            if ($this->debug_file) {
                $strColdef = "";
                for ($pos = 0; $pos < count($cells); $pos ++) {
                    $strColdef .= " | " . safe_val($headlines_found, $pos, "-");
                }
                fprintf($this->debug_file, "COLUMNS: %s\n\n", substr($strColdef, 3));
            }
            return $headlines_found;
        }

        return false;

    }


    // ==============================================================================================
    function ScanForDelimiter($line)
    {

        $delim = false;

        $withoutQuotes = preg_replace('!["][^\\"]+["]!', '', $line);
        $onlydelims = preg_replace('![^,;\t]!', '', $line);

        if (($onlydelims == "") && strlen($withoutQuotes))
            return 'none';

        for ($i = 0; $i < strlen($onlydelims); $i ++) {
            if ($delim && ($delim != $onlydelims[$i]))
                return "error";

            $delim = $onlydelims[$i];
        }
        return $delim;

    }


    // ==============================================================================================
    function SetCsv(&$csvData)
    {

        unset($this->S_root['data']);
        $this->S_csvColumns = false;
        $this->S_root['input'] = $csvData;

        $result = [];
        $lines = explode("\n", $this->S_root['input']);
        $coldef_headline = null;
        $coldef_data = null;
        $used_cols = array();

        if ($this->wc_variants)
            $wc_map = array_flip($this->wc_variants);
        else
            $wc_map = array();

        if (! $this->delimiter) {
            $n = 0;
            foreach ($lines as $zeile => &$line) {
                if (! ($this->delimiter = $this->ScanForDelimiter($line)))
                    continue;
                if ($this->delimiter == "error")
                    return "error";

                $n ++;
                if ($n == 4)
                    break;
            }
        }

        if (! $this->delimiter || ($this->delimiter == 'none'))
            $this->delimiter = '\1';

        foreach ($lines as $zeile => &$line) {
            $line = trim($line);

            if (trim(str_replace($this->delimiter, '', $line)) == '')
                continue;

            $cells = str_getcsv($line, $this->delimiter);
            $num_cols = count($cells);

            if ($num_cols == 0)
                continue;

            if (! $this->S_csvColumns) {
                if ($zeile >= 10)
                    return STS_ERROR_UNKNOWN_FILETYPE;

                if (! $coldef_headline)
                    $coldef_headline = $this->ScanColumDefFromHeadline($cells);

                if (! $coldef_data)
                    $coldef_data = $this->ScanColumDefFromData($cells, $wc_map);

                if ($coldef_headline) // Spalten wurden über Header erkannt
                {
                    if (! $coldef_data || (count($coldef_headline) >= count($coldef_data))) {
                        $this->S_csvColumns = $coldef_headline;
                        continue;
                    }
                } else if ($coldef_data) // Spalten wurden über Datenfeld erkannt
                {
                    if (! $coldef_headline || (count($coldef_data) >= count($coldef_headline))) {
                        $this->S_csvColumns = $coldef_data;
                    }
                }
            }

            if ($this->S_csvColumns) {
                $iTS = array_search('tsnumber', $this->S_csvColumns);
                $iPK = array_search('penta_kennwort', $this->S_csvColumns);

                if (($iTS !== false) && ($iPK === false)) {
                    $this->S_csvColumns[$iTS] = 'penta+TS';
                } else if (($iPK !== false) && ($iTS === false)) {
                    $this->S_csvColumns[$iPK] = 'penta+TS';
                }

                $result[] = $cells;
                foreach ($cells as $i => $data)
                    if (($data != "") && (strtolower($data) != 'nicht zugewiesen'))
                        $used_cols[$i] = true;
            }
        }

        if ($this->debug_file) {
            fclose($this->debug_file);
            $this->debug_file = null;
        }
        $this->S_root['usedCols'] = array_keys($used_cols);
        $this->S_root['data'] = $result;

        return (count($used_cols) == 0) ? STS_ERROR_NO_DATA : STS_NO_ERROR;

    }


    // ==============================================================================================
    function CheckForIdentCol($colMap)
    {

    }


    // ==============================================================================================
    function ApplyColumns()
    {

        $postedCols = $_REQUEST[$this->postName]['cols'];
        $used_cols = &$this->S_root['usedCols'];
        $this->S_csvColMap = [];
        $used_cols = [];

        $i = 0;
        foreach ($postedCols as $iCol => $colname) {
            if ($colname != '-') {
                $this->S_csvColMap[$colname] = $i;
                $used_cols[] = $iCol;
            }
            $i ++;
        }

        if ($this->forUpdate) {
            $this->S_identCol = $this->CheckForIdentCol($this->S_csvColMap);
            if ($this->S_identCol) {
                $uc = array_search($this->S_identCol, $this->S_csvColumns);
                $ui = array_search($uc, $used_cols);
                unset($used_cols[$ui]);
                array_unshift($used_cols, $uc);
                unset($this->S_csvColMap[$this->S_identCol]);

                $this->S_identIndex = $uc;
            }
        }

    }


    // ==============================================================================================
    function GetNumRows()
    {

        return count($this->S_root['data']);

    }


    // ==============================================================================================
    function GetIdentCol()
    {

        return $this->S_identCol;

    }


    // ==============================================================================================
    function GetDataCols()
    {

        return array_keys($this->S_csvColMap);

    }


    // ==============================================================================================
    function GetIdentValue($line)
    {

        if ($line < count($this->S_root['data'])) {
            $iid = $this->S_identIndex;
            return $this->S_root['data'][$line][$iid];
        }
        return false;

    }


    // ==============================================================================================
    function GetIdentValueList()
    {

        return array_column($this->S_root['data'], $this->S_identIndex);

    }


    // ==============================================================================================
    function GetDataLine($line)
    {

        $row = [];
        if ($line < count($this->S_root['data'])) {
            $data = &$this->S_root['data'][$line];
            foreach ($this->S_csvColMap as $col => $i)
                $row[] = $data[$i];
            return $row;
        }
        return false;

    }


    // ==============================================================================================
    function GetDataAssoc($line)
    {

        $row = [];
        if ($line < count($this->S_root['data'])) {
            $data = &$this->S_root['data'];
            foreach ($this->S_csvColMap as $col => $i)
                $row[$col] = $data[$i];
            return $row;
        }
        return false;

    }


    // ==============================================================================================
    function GetCsvData()
    {

        $result = [];
        $nRows = count($this->S_root['data']);
        $iid = $this->S_identIndex;

        $from = 0;
        $to = $nRows;

        foreach ($this->S_root['data'] as $line => $data) {
            if ($this->forUpdate && ($iid !== false)) {
                $id = $this->S_csvColMap[$line][$iid];
            } else
                $id = $line;

            $result[$id] = GetDataLine($l);
        }
        return $result;

    }


    // ==============================================================================================
    function SetExcelFile($filepath)
    {

        return STS_ERROR_NOT_IMPLEMENTED;

    }


    // ==============================================================================================
    function Execute()
    {

        if (empty($_REQUEST[$this->postName]['step']))
            return false;

        reset($_REQUEST[$this->postName]['step']);
        $this->step = key($_REQUEST[$this->postName]['step']);
        unset($_REQUEST[$this->postName]['step']);

        switch ($_REQUEST[$this->postName]['delimiter']) {
            default:
            case 'a':
                $this->delimiter = false;
                break;
            case 'c':
                $this->delimiter = ',';
                break;
            case 's':
                $this->delimiter = ';';
                break;
            case 't':
                $this->delimiter = "\t";
                break;
        }

        switch ($this->step) {
            case 0:
                return 0;

            case 1:
                $filepath = $_FILES[$this->postName]['tmp_name']['file'];
                if ($filepath) {
                    $this->S_UploadedFile = $_FILES[$this->postName]['name']['file'];

                    if (stristr($this->S_UploadedFile, '.xlsx')) {
                        $this->error = $this->SetExcelFile($filepath);
                        break;
                    }

                    $this->errorNo = $this->SetCsv(file_get_contents($filepath));
                } else {
                    $this->errorNo = $this->SetCsv($_REQUEST[$this->postName]['data']);
                }

                unset($_REQUEST[$this->postName]['data']);
                if (count($this->S_csvColumns) != 1)
                    break;

                $this->step = 2;

            case 2:
                $this->ApplyColumns();
                break;

            default:
                return false;
        }
        return ($this->errorNo) ? 0 : $this->step;

    }


    // ==============================================================================================
    function GetHtml_CsvTextarea($width = 600, $height = 400)
    {

        $width -= 15;
        $width_area = $width - 15;
        $height_area = $height - 35;
        $padding = $height / 2 - 20;
        $delim_top = $height_area + 20;
        $delim_left = $width_area - 200;

        return <<<EOT
    <div class="positioneer" style="width:{$width}px;height:{$height}px;">&nbsp;
    <div class="backlabel" id="id_{$this->postName}_dummy" style="width:{$width_area}px;height:{$height_area}px;">
        <div style="position:relative;top:{$padding}px;width:100%;">Datei zum <b>Upload</b> ausgewählt.</div>
    </div>
    <textarea id="id_{$this->postName}_data" name="{$this->postName}[data]" placeholder="Hier können Sie per Copy/Paste die Csv/Excel Tabellen direkt einfügen"
        style="position:absolute;top:5px;left:5px;z-index:5;width:{$width_area}px;height:{$height_area}px;"
        OnInput="document.getElementById('id_{$this->postName}_send').disabled = (this.value == '')"></textarea><br>
    <div style="position:absolute;top:{$delim_top}px;left:{$delim_left}px;"> Trennzeichen: <select style="width:140px;" name="{$this->postName}[delimiter]"><option value="a">-auto detect -</option><option value="c">Komma ","</option><option value="s">Semikolon ";"</option><option value="t">Tabulator "\\t"</option></select></div>
    </div><!--  -->

EOT;

    }


    // -----------------------------------------------------------------------------------------------
    protected function __GetHtml_CsvUploadFile($width, $btnwidth = 150, $caption)
    {

        $infosize = $width - $btnwidth - 5;
        $infoleft = $btnwidth + 5;

        if ($caption == "")
            $caption = 'Datei auswählen';

        return <<<EOT
        <input  type="button" class="csvTool_SendButton" value="{$caption}" style="width:{$btnwidth}px;"
                onClick="document.getElementById('id_{$this->postName}_file').click();" />
        <input  name="{$this->postName}[file]" id="id_{$this->postName}_file" type="file" size="50" class="csvTool_SendFile"
                accept="text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" style="width:{$width}px"
                onChange = "document.getElementById('id_{$this->postName}_text').value = this.value.substr(12);
                            var btnsend=document.getElementById('id_{$this->postName}_send'); if (btnsend) btnsend.disabled=false;
                            var textarea=document.getElementById('id_{$this->postName}_data'); if (textarea) textarea.style.visibility='hidden';
                            var dummydiv=document.getElementById('id_{$this->postName}_dummy'); if (dummydiv) dummydiv.style.visibility='visible';" />
        <input type="text" class="csvTool_SendInfo" readonly id="id_{$this->postName}_text" style="left:{$infoleft}px;width:{$infosize}px;" placeholder="keine Datei ausgewählt" />

EOT;

    }


    function GetHtml_CsvUploadFile($width, $btnWidth = 150, $caption = "")
    {

        return sprintf('<div class="csvTool_SendDiv" style="width:%s">%s</div>', $width, $this->GetHtml_CsvUploadFile($width, $btnWidth, $caption));

    }


    // -----------------------------------------------------------------------------------------------
    protected function __GetHtml_CsvBtnSend($btnWidth = 150, $left = 0, $caption = "")
    {

        if ($caption == "")
            $caption = 'Senden';

        return <<<EOT
<input type="submit" class="csvTool_SendButton" id="id_{$this->postName}_send" name="{$this->postName}[step][1]" value="{$caption}" style="width:{$btnWidth}px;left:{$left}px" disabled />

EOT;

    }


    function GetHtml_CsvBtnSend($width, $btnWidth = 150, $left, $caption = "")
    {

        return <<<EOT
<div class="csvTool_SendDiv" style="width:{$width}px">

EOT
 . $this->GetHtml_CsvBtnSend($caption, $btnWidth, $left - 5) . "\n</div>\n";

    }


    // -----------------------------------------------------------------------------------------------
    function GetHtml_CsvUploadFileAndBtnSend($width, $btnWidth = 150, $captionSelect = '', $captionSend = "")
    {

        $left = $width - $btnWidth + 5;

        return <<<EOT
<div class="csvTool_SendDiv" style="width:{$width}px">

EOT
 . $this->__GetHtml_CsvUploadFile($width - $btnWidth - 25, $btnWidth, $captionSelect) . $this->__GetHtml_CsvBtnSend($btnWidth, $left - 5, $captionSend) . "\n</div>\n";

    }


    // -----------------------------------------------------------------------------------------------
    function WriteHtml_CsvUploadTable($width, $height, $buttonwidth = 150, $captionSelect = '', $captionSend = "")
    {

        echo <<<EOT
    <table class="white softborder" style="width:{$width}px;overflow:hidden;">
      <tr><th style="background-color:#eeeeee">CSV/Excel Daten</th></tr>
      <tr><td style="width:{$width}px;height:{$height}px;">
EOT;

        echo $this->GetHtml_CsvTextarea($width - 8, $height - 8);

        echo "\n  </td></tr>\n  <tr><td>\n";
        echo $this->GetHtml_CsvUploadFileAndBtnSend($width - 20, $buttonwidth, $captionSelect, $captionSend);
        echo "\n  </td></tr>\n  </table>\n";

    }


    // ==============================================================================================
    function SetOutputBuffering($on)
    {

        if ($on)
            $this->OutputBuffer = [];
        else
            $this->OutputBuffer = false;

    }


    // ==============================================================================================
    function OUT($text, $eol = "")
    {

        if (is_array($this->OutputBuffer))
            $this->OutputBuffer[] = $text . $eol;
        else
            echo $text . $eol;

    }


    // ==============================================================================================
    function OUTF()
    {

        if (func_num_args() > 1) {
            $args = func_get_args();
            $format = array_shift($args);

            if (is_array($this->OutputBuffer))

                $this->OutputBuffer[] = vsprintf($format, $args);
            else
                vprintf($format, $args);
        }

    }


    // ==============================================================================================
    function GetContent()
    {

        if (is_array($this->OutputBuffer))
            return implode('', $this->OutputBuffer);
        return "";

    }


    // ==============================================================================================
    function WriteContent()
    {

        if (is_array($this->OutputBuffer))
            foreach ($this->OutputBuffer as $line)
                echo $line;

    }


    // ==============================================================================================
    function CreateCsvValidationForm($addForm = false)
    {

        if ($addForm)
            $this->OUTF('<form name="mainForm" action="%s" method="post">%s', $_SERVER['PHP_SELF'], lf);

        $this->OUT('<div class="bckgrd">', lf);
        $this->OUT(forwardPostVarsAsHiddenInput([
            'pastedCsvText,pasteCsvData'
        ]));
        $this->CreateCsvValidationContent();
        $this->OUT('</div>', lf);
        if ($addForm)
            $this->OUT('</form>', lf);

    }


    function CreateCsvValidationContent()
    {

        $used_cols = $this->S_root['usedCols'];
        $numcols = count($used_cols);
        $colSize = 150;
        $scrollbsize = 10;
        $tblsize = $colSize * $numcols + $scrollbsize;

        $submitname = sprintf('%s[step][%d]', $this->postName, $this->step + 1);
        $backname = sprintf('%s[step][%d]', $this->postName, $this->step - 1);

        // ----------------------------------------------------------------------------------------------

        $this->OUT('<table class="csvTool" style="width:' . $tblsize . 'px;"><thead><tr>', lf);

        $iCol = 0;

        foreach ($used_cols as $nc) {
            $iCol ++;
            $csvCol = $this->S_csvColumns[$nc];
            $width = (($iCol == $numcols) ? $colSize + $scrollbsize : $colSize);

            $this->OUTF('  <th style="width:%dpx"><select name="%s[cols][%d]" id="id_sel_%s_%d" style="width:%dpx" onChange="OnChangeCsvColumn ()">', $width, $this->postName, $nc, $this->postName, $iCol, $colSize - 10);
            $this->OUT('<option value="-">- not used -</option>');
            foreach ($this->selectableCols as $col => $colname) {
                $selected = ($csvCol == $col) ? " selected" : "";
                $this->OUT("<option value=\"$col\"$selected>$colname</option>");
            }
            $this->OUT("</select></th>", lf);
        }

        $btnwidth = ($numcols == 1 ? '100%' : '150px');

        $this->OUT(<<<HEREDOC
    </tr>
  </thead>
  <tfoot><tr>
     <td colspan="{$numcols}" style="text-align:right;background-color:white;">
        <input type="submit" name="{$backname}" value="&lt;&lt; Zurück" style="width:{$btnwidth};margin-right:50px;">
        <input type="submit" name="{$submitname}" value="Weiter &gt;&gt;" style="width:{$btnwidth};margin-right:30px;">
     </td>
  </tfoot>
  <tbody><tr>
    <td colspan="{$numcols}"><div style="overflow:scroll;height:300px;">
      <table>
HEREDOC
);

        foreach ($this->S_root['data'] as $row => $data) {
            $this->OUT('  <tr>');
            foreach ($used_cols as $nc) {
                $this->OUTF('    <td style="width:%dpx">%s</td>%s', $colSize, $data[$nc], lf);
            }
            $this->OUT("  </tr>", lf);
        }
        $this->OUT('</table></div></td></tr></tbody></table>', lf);

    }

    // ==============================================================================================
}

?>
