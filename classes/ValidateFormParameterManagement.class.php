<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 3/1/19
 * Time: 3:33 PM
 */

class ValidateFormParameterManagement {

    function __construct($ladeLeitWartePtr, $requestPtr) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->requestPtr = $requestPtr;
        $this->errors = [];
    }

    public function validateForm() {
        $request = $this->requestPtr;
        //HEADER
        isset($request['ecuVersion']['sts_version']) ? $this->errorPush($this->validate_sts_version($request['ecuVersion']['sts_version']), "ecuVersion[sts_version]") : '';
        isset($request['ecuVersion']['sts_version']) ? $this->errorPush($this->validate_sts_version($request['ecuVersion']['sts_version']), "ecuVersion[sts_version]") : '';
        isset($request['ecuVersion']['href_windchill']) ? $this->errorPush($this->validate_windchill($request['ecuVersion']['href_windchill']), 'ecuVersion[href_windchill]') : '';
        isset($request['ecuVersion']['subversion_suffix']) ? $this->errorPush($this->validate_suffix($request['ecuVersion']['subversion_suffix']), 'ecuVersion[subversion_suffix]') :
            $this->ladeLeitWartePtr->newQuery('ecu_revisions')->update(['subversion_suffix'], '');
        isset($request['ecuVersion']['request']) ? $this->errorPush($this->validate_request_response($request['ecuVersion']['request']), 'ecuVersion[request]') :
            $this->ladeLeitWartePtr->newQuery('ecu_revisions')->update(['request_id'], ['00000000']);
        isset($request['ecuVersion']['response']) ? $this->errorPush($this->validate_request_response($request['ecuVersion']['response']), 'ecuVersion[response]') :
            $this->ladeLeitWartePtr->newQuery('ecu_revisions')->update(['response_id'], ['00000000']);

        //PARAMETERS
        foreach ($request['ecu'] as $key => $value) {
            isset($value['udsId']) ? $this->errorPush($this->validate_udsId($value['udsId']), 'ecu[' . $key . '][udsId]') : '';
            isset($value['size']) && isset($value['startBit']) && isset($value['stopBit']) ?
                $this->errorsPush($this->validate_uds_message($value['size'], $value['startBit'], $value['stopBit']), ['ecu[' . $key . '][size]', 'ecu[' . $key . '][startBit]', 'ecu[' . $key . '][stopBit]']) : '';
            isset($value['protocol']) ? $this->errorPush($this->validate_protocol($value['protocol']), 'ecu[' . $key . '][protocol]') : '';
            isset($value['factor']) ? $this->errorPush($this->validate_factor_offset($value['factor']), 'ecu[' . $key . '][factor]') : '';
            isset($value['offset']) ? $this->errorPush($this->validate_factor_offset($value['offset']), 'ecu[' . $key . '][offset]') : '';
            isset($value['value']) ? $this->errorPush($this->validate_default_constant_value($value['value']), 'ecu[' . $key . '][value]') : '';
            /** String */
            isset($value['value']) && (isset($value['type']) && $value['type'] == 'string')
                ? $this->errorsPush($this->validate_value_string($value['value']), ['ecu[' . $key . '][value]', 'ecu[' . $key . '][type]'])
                : '';
            /** Boolean */
            isset($value['value']) && (isset($value['type']) && $value['type'] == 'bool')
                ? $this->errorsPush($this->validate_value_bool($value['value']), ['ecu[' . $key . '][value]', 'ecu[' . $key . '][type]'])
                : '';
            /** Integer */
            isset($value['value']) && isset($value['size']) && (isset($value['type']) && $value['type'] == 'int')
                ? $this->errorsPush($this->validate_value_integer($value['value'], $value['size']), ['ecu[' . $key . '][value]', 'ecu[' . $key . '][type]'])
                : '';
            isset($value['dyn_token']) ? $this->errorPush($this->validate_dyn_token($value['dyn_token']), 'ecu[' . $key . '][dyn_token]') : '';
            isset($value['start']) && !(isset($value['action']['r']) || isset($value['action']['w']) || isset($value['action']['c'])) ?
                $this->errorsPush(false, ['ecu[' . $key . '][action][r]', 'ecu[' . $key . '][action][w]', 'ecu[' . $key . '][action][c]']) : '';
            //validate copy parameters-------
            isset($value['id']) && isset($value['previous_id']) ? $this->errorPush($this->validate_previous_id_udsId($value['previous_id'], $value['id']), 'ecu[' . $key . '][id]') : '';
            isset($value['udsId']) && isset($value['previous_udsId']) ? $this->errorPush($this->validate_previous_id_udsId($value['previous_udsId'], $value['udsId']), 'ecu[' . $key . '][udsId]') : '';
            //-------------------------------
            /** Hexadecimal Validation */
            /** Integer */
            isset($value['value']) && (isset($value['type']) && $value['type'] == 'int') && isset($value['factor']) && isset($value['offset'])
                ? $this->errorsPush(
                $this->validate_hex_value_integer(
                    $value['value'], $value['bytes'], $value['factor'], $value['offset'], $value['startBit'], $value['stopBit']),
                [
                    'ecu[' . $key . '][value]',
                    'ecu[' . $key . '][type]',
                    'ecu[' . $key . '][bytes]',
                    'ecu[' . $key . '][factor]',
                    'ecu[' . $key . '][offset]',
                    'ecu[' . $key . '][value]',
                    'ecu[' . $key . '][startBit]',
                    'ecu[' . $key . '][stopBit]'
                ]
            )
                : '';
            /** String */
            isset($value['value']) && (isset($value['type']) && $value['type'] == 'string') && isset($value['factor']) && isset($value['offset'])
                ? $this->errorsPush(
                $this->validate_hex_value_string(
                    $value['value'], $value['factor'], $value['offset'], $value['startBit'], $value['stopBit']),
                [
                    'ecu[' . $key . '][value]',
                    'ecu[' . $key . '][type]',
                    'ecu[' . $key . '][bytes]',
                    'ecu[' . $key . '][factor]',
                    'ecu[' . $key . '][offset]',
                    'ecu[' . $key . '][value]',
                    'ecu[' . $key . '][startBit]',
                    'ecu[' . $key . '][stopBit]'
                ]
            )
                : '';
        }

        if (empty($this->errors)) {
            $this->errors[0] = "empty";
        }

        return json_encode($this->errors);
    }

    private function errorPush($flag, $name) {
        if ($flag == false) {
            /* Make unique errors instead of duplicates */
            if (!in_array($name, $this->errors)) {
                array_push($this->errors, $name);
            }
        }
    }

    private function errorsPush($flag, $names) {
        if ($flag == false)
            foreach ($names as $name)
                $this->errorPush($flag, $name);
    }

    private function validate_sts_version($value) {
        $versionLimits = ['<c1' => 'A', '>c1' => 'E', '<c23' => 12, '>c23' => date("y") + 1, '!=c4' => 'X',
            'regex' => [
                '/^[B-Z][0-9]{2}X[0-9A-F]{8}_[0-9]{2}\s*[A-Z]?$/',
                '/^[A-D]1[0-9]X[0-9]{6}_[0-9]{2}\s*[A-Z]?[.]?[0-9]*$/',
                '/^A12X825300_[0-4][0-9]_[0-9]{2}\s*[A-Z]?$/'
            ],
            'new-regex' => '/^[ABCDEF]{1}[1-3]{1}[0-9]{1}[A-Z]{1}([0-9]*)([_]{1}[0-9]{1,8})*$/'
        ];

        if (strlen($value) < 5)
            return false;

        if (substr($value, 0, 2) == '**')
            return true;

        $validation_flag = true;

        $c1 = $value[0];
        $c23 = substr($value, 1, 2);
        $c4 = $value[3];
        $VL = $versionLimits;

        if (($c1 < $VL['<c1']) || ($c1 > $VL['>c1']) || ($c23 < $VL['<c23']) || ($c23 > $VL['>c23']) || ($c4 != $VL['!=c4']))
            $validation_flag = true;

        if ($validation_flag) {
            foreach ($VL['regex'] as $reg)
                if (preg_match($reg, $value))
                    return true;
        }

        if (preg_match($VL['new-regex'], $value))
            return true;

        return false;
    }

    private function validate_windchill($value) {
        $link = "http://windchillapp.streetscooter.local/Windchill";
        $link2 = "http://windchill.streetscooter.eu/Windchill";
        if (substr($value, 0, strlen($link)) == $link || substr($value, 0, strlen($link2)) == $link2) {
            return true;
        } else {
            return false;
        }
    }

    //done: 29bit number validation
    private function validate_request_response($value) {
        if (ctype_xdigit($value) && hexdec($value) <= hexdec('0x20000000')) {
            return true;
        } else {
            return false;
        }
    }

    //done: 16bit integer value as hex value
    private function validate_udsId($value) {
        if (ctype_xdigit($value) && hexdec($value) <= hexdec('0x10000')) {
            return true;
        } else {
            return false;
        }
    }

    private function validate_suffix($value) {
        return strlen($value) >= 2 ? true : false;
    }

    private function validate_uds_message($size, $start, $stop) {
        return $start <= $stop && $stop <= ($size * 1024) ? true : false;
    }

    private function validate_factor_offset($value) {
        if (is_float($value)) {
            return is_float($value * 1000) ? false : true;
        } else {
            return true;
        }
    }

    private function validate_default_constant_value($value) {
        return $value != "" ? true : false;
    }

    private function validate_dyn_token($value) {
        return $value != '%-leer-%' ? true : false;
    }

    private function validate_protocol($value) {
        return $value == 1 || $value == 2 ? true : false;
    }

//validate copy parameters----
    private function validate_previous_id_udsId($previous, $value) {
        return $previous != $value ? true : false;
    }

//-----------------------------
    private function validate_value_string($value) {
        $string = preg_replace('/\P{L}+/u', '', $value);

        return !empty($string);
    }

    private function validate_value_bool($value) {
        return ($value == 'true' || $value == 'false');
    }

    private function validate_value_integer($value, $size) {
        switch (true) {
            case (!$size || $size >= 4):
                return ((-2147483648 <= $value) && ($value <= 2147483648));
                break;
            case $size == 3:
                return ((-8388608 <= $value) && ($value <= 8388608));
                break;
            case $size == 2:
                return ((-32768 <= $value) && ($value <= 32768));
                break;
            case $size == 1:
                return ((-128 <= $value) && ($value <= 128));
                break;
            default:
                return false;
        }
    }

//-----------------------------
    private function validate_hex_value_integer($value, $bytes, $factor, $offset, $startBit, $stopBit) {
        $bits = $bytes * 1024;
        $hexValue = (dechex($value) * $factor) + $offset;
        $hexStartBit = dechex($startBit);
        $hexStopBit = dechex($stopBit);

        if ($hexValue < $bits)
            return false;

        return (($hexStartBit <= $hexValue) && ($hexValue <= $hexStopBit));
    }

    private function validate_hex_value_string($value, $factor, $offset, $startBit, $stopBit) {
        $hexValue = (bin2hex($value) * $factor) + $offset;

        return (($startBit <= $hexValue) && ($hexValue <= $stopBit));
    }
}