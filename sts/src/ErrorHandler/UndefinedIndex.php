<?php

namespace App\ErrorHandler;
trait UndefinedIndex
{
    public static function errorHandlerCatchUndefinedIndex($errno, $errstr, $errfile, $errline)
    {
        // We are only interested in one kind of error
        if (strpos($errstr, 'Undefined index') !== false) {
            //We throw an exception that will be catched in the test
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
        return false;
    }

    public static function setErrorHandlerCatchUndefinedIndex()
    {
        set_error_handler([undefinedIndex::class, "errorHandlerCatchUndefinedIndex"]);
    }
}

?>