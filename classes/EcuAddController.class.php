<?php


class EcuAddController extends SymfonyBaseController
{
    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user)
    {   parent::__construct($ladeLeitWartePtr, $container, $requestPtr, 'ecu',
        ['css/select2.min.css'], ['js/select2.full.min.js'], 'engg');

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