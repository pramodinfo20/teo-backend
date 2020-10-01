<?php


namespace App\Service\Admin\Keys;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class Keys
{
    const SERVER_KEYS_LOCATION = '/home/.keys';
    const SERVER_PRIVATE_KEY_NAME = 'private.key';
    const SERVER_PUBLIC_KEY_NAME = 'public.key';
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * Check File Format
     *
     * @param String $key
     *
     * @return bool
     */
    public function checkKeyFileFormat(String $key): bool
    {
        $separator = "\r\n";
        $line = strtok($key, $separator);
        $validBegin = false;
        $validEnd = false;
        while ($line !== false) {
            if (strpos($line, "-----BEGIN") !== false) {
                $validBegin = true;
            } elseif (strpos($line, "-----END") !== false) {
                $validEnd = true;
            }
            $line = strtok($separator);
        }

        return ($validBegin && $validEnd);
    }

    /**
     * Prepare Valid Key Format
     *
     * @param String $key
     *
     * @return null|String
     */
    public function prepareKeyFormat(String $key): ?String
    {
        $separator = "\r\n";
        $line = strtok($key, $separator);
        $validBegin = false;
        $validEnd = false;
        $formattedKey = '';
        while ($line !== false) {
            if (strpos($line, "-----BEGIN") !== false) {
                $line = strtok($separator);
                $validBegin = true;
                continue;
            } elseif (strpos($line, "-----END") !== false) {
                $line = strtok($separator);
                $validEnd = true;
                continue;
            } elseif ($line == "") {
                $line = strtok($separator);
                continue;
            }

            if ($validBegin) {
                $formattedKey .= $line;
            }

            $line = strtok($separator);
        }

        if ($validBegin && $validEnd) {
            return substr($formattedKey, 0, strripos($formattedKey, '='));
        } else {
            return null;
        }
    }
}