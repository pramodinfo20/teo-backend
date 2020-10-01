<?php
/**
 * pageoutput.class.php
 * Klassen für PageOutput
 * @author Pradeep Mohan
 */

/**
 * Pageoutput interface
 *
 * $options ist ein Liste von options-worte, die den Inhalt für bestimmt Zwecke noch verändern,
 * z.B. für eine Druckansicht oder Email-Textkörper:
 *
 *  nomenu:     Die Ausgabe des Menüs wird unterdrückt
 *  nologo:     Die Ausgabe der Logos werden unterdrückt
 *  nodebug:    Debug-Werkzeuge werden verhindert
 *  nolinks:    Weiterführende Links werden nicht erzeugt.
 *
 */
interface PageOutput {
    function printContent($options = "");
}

/**
 * Display class to output the HTML
 */
class DisplayHTML implements PageOutput {
    /**
     * Holds the HTML content to be displayed
     *
     * @var string $contentHTML
     */
    protected $contentHTML;
    /**
     * @var LadeLeitWarte
     */
    protected $ladeLeitWartePtr;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr = null) {
        $this->contentHTML = "";
        if ($ladeLeitWartePtr) $this->ladeLeitWartePtr = $ladeLeitWartePtr;
    }

    /**
     * echo contents of the $this->contentHTML variable.
     */
    function printContent($options = "") {
        echo $this->getContent($options);
    }

    /**
     * return contents of the $this->contentHTML variable.
     */
    function getContent($options = "") {
        return $this->contentHTML;
    }
}

?>