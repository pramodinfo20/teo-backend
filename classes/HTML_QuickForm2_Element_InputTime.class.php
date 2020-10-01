<?php
/**
 * Base class for <input> elements
 */
require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="time" /> elements
 *
 */
class HTML_QuickForm2_Element_InputTime extends HTML_QuickForm2_Element_Input {
    protected $persistent = true;

    protected $attributes = array('type' => 'time');
}

?>
