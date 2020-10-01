<?php

/**
 * GrafanaApi.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle Grafana API functions
 */
class GrafanaApi {

    protected $ladeLeitWartePtr;
    protected $user;
    protected $adminkey;
    protected $ch;
    // protected $displayHeader;
    // protected $requestPtr;
    function __construct($ladeLeitWartePtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->user = $user;
        $this->adminkey = 'eyJrIjoiU1VmcW9YZWpiMU0zM09WaVV3aW9UMkFJOEFMMzVRQ2giLCJuIjoiYWRtaW4iLCJpZCI6MX0=';
        $this->ch = NULL;
    }

    function getPostData() {

        $data = [
            'name' => $this->user->getUserName(),
            'role' => 'Viewer'
        ];
        return json_encode($data);
    }

    function setupCurl() {
        $this->ch = curl_init();
        $timeout = 5;
        $headers = array(
            'Authorization: Bearer ' . $this->adminkey,
            'Accept: application/json',
            'Content-Type: application/json'
        );

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_COOKIE, "sts_grafana=".urlencode("eyJrIjoiM1pFUVB2VVNOdWtoR0ZvVzJ6UUlzbWxZRmE1NzBpaVQiLCJuIjoiYWRtaW4iLCJpZCI6MX0="));
        // curl_setopt($ch, CURLOPT_URL, 'http://localhost/grafana/api/dashboards/home');

        // curl_setopt($ch, CURLOPT_POSTFIELDS, $js_data);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        /*$data = curl_exec($this->ch);
        //curl_close($this->ch);
        return $data;*/
    }

    function deleteExistingKey() {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch, CURLOPT_URL, 'https://10.12.54.170:3000/api/auth/keys');

        $data_json = curl_exec($this->ch);
        $data = json_decode($data_json, true);
        $usernames = array_column($data, 'name');
        $key = array_search($this->user->getUserName(), $usernames);

        if ($key !== false) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->ch, CURLOPT_URL, 'https://10.12.54.170:3000/api/auth/keys/' . $data[$key]['id']);
            $data_json = curl_exec($this->ch);
            $data = json_decode($data_json, true);

            if (empty($data)) return false;
            else if (sizeof($data) == 1 && preg_replace('/\s+/', '_', strtolower($data['message'])) == 'api_key_deleted') return true;
            else return false;
        }
    }

    function generateNewKey() {
        if ($this->deleteExistingKey()) {
            //do nothing
        }
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_URL, 'https://10.12.54.170:3000/api/auth/keys');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->getPostData());
        $data_json = curl_exec($this->ch);
        $data = json_decode($data_json, true);
        return $data['key'];
    }

    function isKeyValid($key) {
        $headers = array(
            'Authorization: Bearer ' . $key,
            'Accept: application/json',
            'Content-Type: application/json'
        );

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch, CURLOPT_URL, 'https://10.12.54.170:3000/api/dashboards/home');
        $data_json = curl_exec($this->ch);
        $data = json_decode($data_json, true);
        curl_close($this->ch);
        $this->setupCurl();

        if (empty($data)) return false;
        else if (sizeof($data) == 1 && preg_replace('/\s+/', '_', strtolower($data['message'])) == 'invalid_api_key') return false;
        else return true;
    }

    function authUserWithKey() {
        //$key=$this->ladeLeitWartePtr->newQuery('grafana_keys')->where('user_id','=',$this->user->getUserId())->getVal('grafana_key');
        if ($this->user->user_can('grafana_admin')) {
            $key = $this->adminkey;
            //delete existing cookie
            setcookie('sts_grafana', '', time() - 3600);
            //set new cookie
            setcookie('sts_grafana', $key, time() + 86400);
        } else {
            $key = $_COOKIE['sts_grafana'];
            if (empty($key) || !$this->isKeyValid($key)) {
                $key = $this->generateNewKey();
                if (!empty($key)) {
                    //delete existing cookie
                    setcookie('sts_grafana', '', time() - 3600);
                    //set new cookie
                    setcookie('sts_grafana', $key, time() + 86400);
                }
            }
        }
    }
}
