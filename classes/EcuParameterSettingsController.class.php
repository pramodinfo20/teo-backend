<?php


class EcuParameterSettingsController extends SymfonyBaseController {
    public function __construct($ladeLeitWartePtr, $container, $requestPtr) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, 'configuration',
            [], ['js/jquery.collection.js']);

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

    public function ajaxGetListOfAvailableOptionsForProperty() {

        $actionParameter = "vehicles/configuration/" . $_GET['propertyId'] . "/optionsForProperty";
        $result = $this->getMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_GET, $actionParameter)
            ->sentRequest();

        echo $result;
    }
}