<?php

namespace App\Service\Users;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Entity\Users;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;

class Settings
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Parameter constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Load last selected sw for userId and ecuId
     *
     * @param Users $user
     * @param int   $ecu
     *
     * @return int|null
     */
    public function loadLastSelectedSwByUserIdAndEcuId(Users $user, int $ecu): ?int
    {
        $swId = null;

        $entityManagerUserSettings = $this->entityManager->getRepository(UserSettings::class);
        $entityManagerSw = $this->entityManager->getRepository(EcuSwVersions::class);

        $settings = $entityManagerUserSettings->findOneBy(['stsUserid' => $user->getId()]);

        if ($settings) {
            /* IMPORTANT! Safe unserialize */
            $sws = unserialize($settings->getSettings(), ['allowed_classes' => false]);

            if (isset($sws['sw_version'][$ecu]['sw'])) {
                $swSaved = $sws['sw_version'][$ecu]['sw'];
                $swDatabase = $entityManagerSw->find($swSaved);
                $swId = ($swDatabase) ? $swSaved : null;
            }
        }

        return $swId;
    }

    /**
     * Save last selected sw for userId and ecuId
     *
     * @param Users             $user
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     *
     * @return void
     */
    public function saveSelectedSwForUserIdAndEcuId(Users $user, ConfigurationEcus $ecu, EcuSwVersions $sw): void
    {
        $entityManagerUserSettings = $this->entityManager->getRepository(UserSettings::class);
        $entityManagerUsers = $this->entityManager->getRepository(Users::class);

        $settings = $entityManagerUserSettings->findOneBy(['stsUserid' => $user->getId()]);

        if ($settings) {
            /* IMPORTANT! Safe unserialize */
            $sws = unserialize($settings->getSettings(), ['allowed_classes' => false]);
            $sws['sw_version'][$ecu->getCeEcuId()]['sw'] = $sw->getEcuSwVersionId();

            $settings->setSettings(serialize($sws));

            $this->entityManager->persist($settings);
            $this->entityManager->flush();
        } else {
            $sws['sw_version'][$ecu->getCeEcuId()]['sw'] = $sw->getEcuSwVersionId();

            $setting = new UserSettings();
            $setting->setStsUserid($entityManagerUsers->find($user->getId()));
            $setting->setSettings(serialize($sws));

            $this->entityManager->persist($setting);
            $this->entityManager->flush();
        }
    }
}