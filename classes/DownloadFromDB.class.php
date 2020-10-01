<?php

/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/19/19
 * Time: 12:38 PM
 */
class DownloadFromDB extends PageController {


    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->printContent();
    }

    function printContent() {
        if (isset($_POST["type"])) {

            switch ($_POST["type"]) {
                case "downloadSignature":
                    $this->downloadSignature();
                    break;
                case "downloadKeys":
                    $this->downloadKeys();
                    break;
                case "downloadPublicKey":
                case "downloadPrivateKey":
                    $this->downloadServerKeys($_POST["type"]);
                    break;
                default:
                    break;
            }
        }
    }

    function checkIfKeyExists() {
        return $this->ladeLeitWartePtr->newQuery("user_key")->where("sts_userid", "=", $_SESSION["sts_userid"])->orderBy("version", "DESC")->limit("1")->get("signature") == null ? false : true;
    }

    function checkIfKeysExist() {
        return $this->ladeLeitWartePtr->newQuery("server_keys")->get("id") == null ? false : true;
    }

    function downloadSignature() {
        if (isset($_POST["downloadSignature"])) {
            if ($this->checkIfKeyExists()) {
                $version = $this->ladeLeitWartePtr->newQuery("server_keys")->orderBy("id", "DESC")->limit("1")->getVal("id");

                $result = $this->ladeLeitWartePtr->newQuery("user_key")->where("sts_userid", "=", $_SESSION["sts_userid"])->where("version", "=", $version)->get("signature, size, type");
                header("Content-Type: text/plain");
                header("Content-Length: " . $result[0]['size']);
                header("Content-Disposition: attachment;filename='signature." . $result[0]["type"] . "'");

                echo $result[0]['signature'];
            }
        }
    }

    function downloadKeys() {
        if (isset($_POST["downloadKeys"])) {
            $version = $this->ladeLeitWartePtr->newQuery("server_keys")->orderBy("id", "DESC")->limit("1")->getVal("id");

            $result = $this->ladeLeitWartePtr->newQuery("user_key")->where("sts_userid", "=", $_POST["sts_userid"])->where("version", "=", $version)->get("signature, size, type");
            header("Content-Type: text/plain");
            header("Content-Length: " . $result[0]['size']);
            header("Content-Disposition: attachment;filename='signature." . $result[0]["type"] . "'");

            echo $result[0]['signature'];
        }
    }

    function downloadServerKeys($type) {
        if ($this->checkIfKeysExist()) {
            $key = $type == 'downloadPrivateKey' ? 'private' : 'public';
            $result = $this->ladeLeitWartePtr->newQuery("server_keys")->orderBy("id", "DESC")->limit("1")->get($key . "_key as key, " . $key . "_size as size");
            header("Content-Type: text/plain");
            header("Content-Length: " . $result[0]['size']);
            header("Content-Disposition: attachment;filename='" . $key . ".gpg'");

            echo $result[0]['key'];
        }
    }
}