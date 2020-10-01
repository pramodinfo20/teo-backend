<?php
require_once __DIR__."/../vendor/autoload.php";

use Symfony\Component\Yaml;
use Symfony\Component\Yaml\Exception;


class LegacyTranslations {
    private $translationsLocation;
    private $language;
    private $callbackLanguage;
    private $translations;
    private $files;
    private $languageCookieName;


    function __construct() {
        $this->translationsLocation =  $GLOBALS['config']->get_property('legacyTranslationsLocation', '/var/www/html/translations/domains');
        $this->languageCookieName = $GLOBALS['config']->get_property('languageCookieName', 'selectedSystemLanguage');

        if (isset($_COOKIE[$this->languageCookieName])) {
            $this->language = $_COOKIE[$this->languageCookieName];
        }
         else {
             $this->language = $GLOBALS['config']->get_property('language', 'de');
         }


        $this->callbackLanguage = 'en';
        $this->files = array_unique(array_map( function ($name) {
            return explode(".", $name)[0];
        }, array_diff(scandir($this->translationsLocation), array('.', '..'))));

        foreach ($this->files as $fileName) {
            try {
                if (file_exists("$this->translationsLocation/$fileName.$this->language.yml")) {
                    $this->translations[$fileName] = Yaml\Yaml::parse(file_get_contents("$this->translationsLocation/$fileName.$this->language.yml"));
                } else {
                    $this->translations[$fileName] = Yaml\Yaml::parse(file_get_contents("$this->translationsLocation/$fileName.$this->callbackLanguage.yml"));
                }
            } catch (Exception\ParseException $exception) {
                printf('Unable to parse the YAML string: %s', $exception->getMessage());
            }
        }
    }

    public function getTranslationsForDomain($domain = 'messages') {
        return $this->translations[$domain];
    }

    public function getSelectedLanguage() {
        return $this->language;
    }
}