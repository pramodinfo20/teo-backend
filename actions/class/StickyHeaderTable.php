<?php

class AClass_StickyHeaderTable {
    protected $pre_header = null;
    protected $header;
    protected $table;
    protected $height = 400;
    protected $row_ids;
    protected $row_attr = [];
    protected $cell_attr = [];
    protected $topX = 0;
    protected $topY = 0;

    public $fill_up_lines = 0;
    public $name;
    public $table_class;

    const LOCAL_STYLE = <<<HEREDOC
        table.stcytbl {width: auto; border: none;}
        table.stcytbl thead tr {background-color: #ccc; }
        table.stcytbl thead tr td {vertical-align: top; border: 1px solid #888;}
        .stcytbl-dummy  {width:16px; padding: 0; margin: 0;}
        .stcytbl-back   {border: 2px solid #888; }
        .stcytbl.scroll {margin-top: -2px; }
HEREDOC;

    const LOCAL_JS = <<<HEREDOC
    var stcytbl_hdrMinWidth = [];
    var stcytbl_hdrNewWidth = [];
    var stcytbl_sum = 0;

    function stcytbl_update()
    {
        var     hdrChanged = false;

        stcytbl_sum = 0;
        $('table.stcytbl tbody tr:nth-child(1) td').each(function(index, td){
            var w = $(this).width();
            var h = stcytbl_hdrMinWidth[index];
            if (h>w) {
                m = h;
                $(this).width(m);
            }else{
                hdrChanged=true;
                m = w;
            }
            stcytbl_hdrNewWidth[index] = m;
        });
        if (hdrChanged) {
            $('table.stcytbl tr.stcyhdr td').each(function(index, td){
                $(this).width(stcytbl_hdrNewWidth[index]);
            });
        }

        var tblWidth = $('table.stcytbl-header').width();
        $('.stcytbl-scroll').width(tblWidth);
        $('.stcytbl-back').width(tblWidth);

        var tblHeight = $('.stcytbl-back').height();
        var bodyHeight = tblHeight - $('.stcytbl-header').height();
        $('.stcytbl-scroll').height(bodyHeight);

        DebugOut (stcytbl_hdrMinWidth);
        DebugOut (stcytbl_hdrNewWidth);
        DebugOut (tblWidth);
    }


    $(document).ready(function()
    {
        $('table.stcytbl thead td').each(function(index, td){
            var w = $(this).width();
            stcytbl_hdrMinWidth.push(w);
            stcytbl_hdrNewWidth.push(w);
        });
        stcytbl_update();
    });

    $(window).resize(stcytbl_update);
HEREDOC;

    //#########################################################################
    function __construct($table_class = "", $name = 'STCYTBL', $header = [], $table = []) {
        global $pageController;

        $this->name = $name;
        $this->table_class = $table_class;
        $this->fill_up_lines = $fill_up_lines;

        $this->setHeader($header);
        $this->setTable($table);
    }

    //#########################################################################
    function setHeight($height, $fill_up_lines = null) {
        $this->height = $height;
        if (isset ($fill_up_lines))
            $this->fill_up_lines = $fill_up_lines;
    }

    //#########################################################################
    function setTableClass($table_class) {
        $this->table_class = $table_class;
    }

    //#########################################################################
    function setPreHeader($preHeader) {
        $this->pre_header = $preHeader;
    }

    //#########################################################################
    function setHeader($header) {
        $this->header = $header;
    }

    //#########################################################################
    function setTable($table) {
        $this->table = $table;
        $this->topY = count($this->table);
        $this->topX = count($this->table[$this->topY]);
    }

    //#########################################################################
    function newRow($row_id = null, $row_attr = null) {
        if (count($this->table)) {
            $this->topY++;
            $this->table[$this->topY] = [];
            $this->topX = 0;
        }

        if (isset($row_id))
            $this->row_ids[$this->topY] = $row_id;

        if (isset($row_attr))
            $this->row_attr[$this->topY] = $row_attr;
    }

    //#########################################################################
    function setCellXY($x, $y, $content, $attributes = null) {
        if (!isset ($y))
            $y = $this->topY;
        if (!isset ($x))
            $x = $this->topX;

        $this->table[$y][$x] = $content;

        if (isset ($attributes))
            $this->setCellAttributeXY($x, $y, $attributes);
    }

    //#########################################################################
    function setCell($x, $content, $attributes = null) {
        $this->setCellXY($x, $this->topY, $content, $attributes);
    }

    //#########################################################################
    function appendCellXY($x, $y, $content) {
        if (!isset ($y))
            $y = $this->topY;
        if (!isset ($x))
            $x = $this->topX;

        $this->table[$y][$x] .= $content;
    }

    //#########################################################################
    function appendCell($x, $content) {
        $this->appendCellXY($x, $this->topY, $content);
    }

    //#########################################################################
    function addCell($content, $attributes = null) {
        $this->setCellXY($this->topX, $this->topY, $content, $attributes);
        $this->topX++;
    }

    //#########################################################################
    function setCellAttributeXY($x, $y, $attributes) {
        if (!isset ($this->cell_attr[$x][$y]))
            $this->cell_attr[$x][$y] = [];

        if (is_array($attributes)) {
            foreach ($attributes as $id => $attr)
                $this->cell_attr[$x][$y][$id] = $attr;
        } else {
            $this->cell_attr[$x][$y] = $attributes;
        }
    }

    //#########################################################################
    function getCellAttributeXY($x, $y) {
        $result = "";
        if (is_array($this->cell_attr[$x][$y])) {
            foreach ($this->cell_attr[$x][$y] as $id => $attr)
                $result .= empty ($attr) ? " $id" : " $id=\"$attr\"";
        } else
            if (isset ($this->cell_attr[$x][$y])) {
                $result = " " . $this->cell_attr[$x][$y];
            }
        return $result;
    }

    //#########################################################################
    function setCellAttribute($x, $attributes) {
        $this->setCellAttributeXY($x, $this->topY, $attributes);
    }

    //#########################################################################
    function SetupHeaderFiles($displayheader) {
        $displayheader->enqueueLocalStyle(self::LOCAL_STYLE);
        $displayheader->enqueueJs('sts-stickytable', '/js/sts-stickytable.js');
    }

    //#########################################################################
    function WriteHtml_Header($header = null) {
        if (isset($header))
            $this->setHeader($header);


        echo <<<HEREDOC
      <div id="id_orders_head" class="stcytbl-header-back">
        <table class="stcytbl stcytbl-header  {$this->table_class}">
          <thead>
HEREDOC;

        if (isset ($this->pre_header)) {
            echo '
            <tr>';

            $m = 0;
            foreach ($this->pre_header as $n => $cell) {
                $d = $n - $m;
                $m = $n;
                echo "<td colspan=\"$d\">$cell</td>";
            }
            echo <<<HEREDOC
              <td class="stcytbl-dummy"></td>
            </tr>
HEREDOC;
        }

        echo '
            <tr class="stcytbl-header">';

        foreach ($this->header as $cell) {
            if (strncasecmp($cell, "<td", 3))
                echo "<td>$cell</td>";
            else
                echo $cell;
        }
        echo <<<HEREDOC

              <td class="stcytbl-dummy"></td>
            </tr>
          <thead>
        </table>
      </div>
HEREDOC;
    }

    //#########################################################################
    function WriteHtml_Body($table = null) {
        if (isset ($table))
            $this->setTable($table);


        echo <<<HEREDOC
      <div id="id_{$this->name}-scroll" style="overflow-y: scroll; overflow-x: visible; height: {$this->height}px;" class="stcytbl-scroll" >
        <table class="stcytbl stcytbl-body {$this->table_class}" id="id_{$this->name}-table">
          <tbody>

HEREDOC;

        $r = 0;
        foreach ($this->table as $row) {
            $data_id = (isset ($this->row_ids[$r])) ? " data-id=\"{$this->row_ids[$r]}\"" : '';
            $attributes = "";

            if (isset ($this->row_attr[$r])) {
                if (is_array($this->row_attr[$r])) {
                    foreach ($this->row_attr[$r] as $id => $attr)
                        $attributes .= " $id=\"$attr\"";
                } else
                    $attributes = " {$this->row_attr[$r]}";
            }
            $rp = $r + 1;
            echo "
            <tr data-row=\"{$rp}\"$data_id id=\"id_{$this->name}-row-{$rp}\"$attributes>";

            foreach ($row as $c => $cell) {
                if (strncasecmp($cell, "<td", 3)) {
                    if (isset ($this->cell_attr[$c][$r]))
                        $attr = $this->getCellAttributeXY($c, $r);
                    else
                        $attr = "";

                    echo "<td$attr>$cell</td>";
                } else
                    echo $cell;
            }
            $r++;
            echo "
            </tr>";
        }

        if ($this->fill_up_lines) {
            $line = str_repeat("<td>&nbsp;</td>", count($this->header));

            for (; $r < $this->fill_up_lines; $r++) {
                echo "
            <tr>$line</tr>";
            }
        }

        echo <<<HEREDOC

          </tbody>
        </table>
      </div>
HEREDOC;
    }

    //#########################################################################
    function WriteHtml_Content($table = null, $header = null) {
        if (!isset ($table_class))
            $table_class = &$this->table_class;

        echo "
    <div id=\"id_{$this->name}-background\" class=\"stcytbl-back\" style=\"height:{$this->height}px\">";
        $this->WriteHtml_Header($header, $table_class);
        $this->WriteHtml_Body($table, $table_class);
        echo "
    </div>";


    }
}

?>