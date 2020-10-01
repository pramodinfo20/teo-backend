<?php
/**
 * Base class for <input> elements
 */
require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="number" /> elements
 *
 */
class HTML_QuickForm2_Element_InputNumber extends HTML_QuickForm2_Element_Input {
    protected $persistent = true;

    protected $attributes = array('type' => 'number');
}

?>
