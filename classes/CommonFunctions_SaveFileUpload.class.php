<?php

/**
 * CommonFunctions_SaveFileUpload.class.php
 * Klasse Datein hochzuladen und speichern
 * http://php.net/manual/en/features.file-upload.php
 * @author Pradeep Mohan
 */

/**
 * Class to handle file uploads and save the file
 */
class CommonFunctions_SaveFileUpload {
    function __construct($uploaded_file, $save_location, $saveas, $ext) {
        $this->uploaded_file = $uploaded_file;
        $this->save_location = $save_location;
        $this->saveas = $saveas;

        if (is_array($ext)) $this->expected_mime_types = $ext;
        else if (!empty($ext)) $this->expected_mime_types[] = $ext;

        $this->defined_mime_types = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
        ];
        if (!empty($this->expected_mime_types)) {
            foreach ($this->expected_mime_types as $mime_type) {
                $this->check_for_mime[$mime_type] = $this->defined_mime_types[$mime_type];
            }
        } else
            $this->check_for_mime = $this->defined_mime_types;

    }

    function checkError() {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (!isset($_FILES[$this->uploaded_file]['error']) || is_array($_FILES[$this->uploaded_file]['error']))
            throw new RuntimeException('Ungültige Datei!');

        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES[$this->uploaded_file]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Keine Datei hochgeladen.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Datei zu Groß.');
            default:
                throw new RuntimeException('Unbekannte Fehler.');
        }
    }

    function checkMime() {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $this->ext = array_search(
                $finfo->file($_FILES[$this->uploaded_file]['tmp_name']),
                $this->check_for_mime,
                true))
            throw new RuntimeException('Ungültige Datei!');
    }

    function saveFile() {
        $this->checkError();
        $this->checkMime();

        if (!empty($this->saveas)) {
            $safe_file_name = filter_var($this->saveas, FILTER_SANITIZE_STRING);
            //https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
            $safe_file_name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $safe_file_name);
            // Remove any runs of periods (thanks falstro!)
            $safe_file_name = mb_ereg_replace("([\.]{2,})", '', $safe_file_name);
        } else $safe_file_name = sha1_file($_FILES[$this->uploaded_file]['tmp_name']);
        if (!move_uploaded_file(
            $_FILES[$this->uploaded_file]['tmp_name'],
            sprintf('/var/www/teo_exceptions/%s.%s',
                $safe_file_name,
                $this->ext
            )))
            return false;

        return $safe_file_name;
    }
}