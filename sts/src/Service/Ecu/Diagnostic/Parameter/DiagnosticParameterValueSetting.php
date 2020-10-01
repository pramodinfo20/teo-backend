<?php

namespace App\Service\Ecu\Diagnostic\Parameter;

//use

use App\Entity\ConfigurationEcus;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class DiagnosticParameterValueSetting
{
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

    public function saveSupportFlagById(ConfigurationEcus $ecu, int $support)
    {
        $ecu->setDiagnosticSoftwareSupportsStsOdx2ForThisEcu($support);
        $this->entityManager->persist($ecu);
        $this->entityManager->flush();
    }
}