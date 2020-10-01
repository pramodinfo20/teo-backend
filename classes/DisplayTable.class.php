<?php

/**
 * Class for the HTML table, constructor parameters to be an array of objects
 */
class DisplayTable extends DisplayHTML {

    /**
     * Outputs an array of objects as a table
     *
     * @param array $theobjects
     *          An array of objects are passed to the function, first array element has to hold header for the table
     */
    function __construct($theobjects, $attributes = null) { //removed $displayfields='' as it might not be necessary
        $this->contentHTML = "";
        $this->colWidths = null;
        $this->cssColWidths = null;
        $tableattributes = '';
        if (is_array($attributes))
            foreach ($attributes as $atkey => $atval) {
                if ($atkey == "csswidths")
                    $this->cssColWidths = $atval;
                else if ($atkey != 'widths') //@todo why not check for key==widths???
                    $tableattributes .= " " . $atkey . "=" . $atval . " ";
                else
                    $this->colWidths = $atval;
            }
        unset($atkey);
        unset($atval);
        if (array_key_exists('headingone', $theobjects[0])) {
            $length = sizeof($theobjects [0]);
            $headings = array_shift($theobjects);
            $this->contentHTML = "<table $tableattributes ><thead>\n<tr>";

            /**
             * $headings ["headingone"] can either be an array of sub arrays of heading labels and attributes
             * or just an array of all the headings
             */
            foreach ($headings ["headingone"] as $key => $value) {

                $this->contentHTML .= "<th ";
                if (isset($this->cssColWidths[$key]))
                    $this->contentHTML .= "style=\"width:" . $this->cssColWidths[$key] . "px\" ";
                if (isset($this->colWidths[$key]))
                    $this->contentHTML .= "width=\"" . $this->colWidths[$key] . "\" ";
                if (isset($value[1]) && is_array($value[1]))
                    foreach ($value [1] as $atkey => $atval)
                        $this->contentHTML .= $atkey . "=" . $atval . " ";
                if (is_array($value))
                    $this->contentHTML .= ">" . $value [0] . "</th>";
                else
                    $this->contentHTML .= ">" . $value . "</th>";
            }
            $this->contentHTML .= "</tr>\n</thead><tbody>\n";
            if (!empty ($headings ["headingtwo"])) {
                $this->contentHTML .= "<tr>";
                foreach ($headings ["headingtwo"] as $value) {
                    $this->contentHTML .= "<th>" . $value . "</th>";
                }
                $this->contentHTML .= "</tr>\n</thead><tbody>";
            }

        } else
            $this->contentHTML = "<table $tableattributes ><tbody>";

        foreach ($theobjects as $theobject) {
            $this->contentHTML .= "<tr>";
            $rowdata = $theobject;
            foreach ($rowdata as $key => $value) {
                if (is_array($value)) {
                    $this->contentHTML .= "<td ";

                    if (isset($value['attributes'])) {
                        foreach ($value['attributes'] as $attribname => $attribval)
                            $this->contentHTML .= $attribname . '="' . $attribval . '" ';
                        $this->contentHTML .= " >" . $value['tdcontent'] . "</td>";

                    }


                } else {
                    $this->contentHTML .= "<td ";
                    /* doesnt make a difference since the browser takes the attributes in th and uses it for the table..
                    if(isset($this->colWidths[$key]))
                      $this->contentHTML .= " width=\"" . $this->colWidths[$key]. "\" ";
                    */
                    $this->contentHTML .= " >" . $value . "</td>";
                }

            }
            $this->contentHTML .= "</tr>\n";
        }
        $this->contentHTML .= "</tbody></table>";
    }
}

?>