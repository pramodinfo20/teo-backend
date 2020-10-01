<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractService
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }
}