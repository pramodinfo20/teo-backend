<?php

namespace App\Form\EventListener\Parameters\Header;

use App\Entity\EcuSwVersions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FixSuffixListener implements EventSubscriberInterface
{
    /**
     * @var EcuSwVersions[]
     */
    private $ecuSoftwareVersions;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->ecuSoftwareVersions = $entityManager->getRepository(EcuSwVersions::class)->findAll();
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $header = $event->getData();
        $form = $event->getForm();

        if ($header->getSubversionSuffix() != null) {
            $form->add('subversionSuffix', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2]),
                ]
            ]);
            $form->add('swVersion', TextType::class, [
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    /* DISABLE SW NAME VALIDATION RULES */
//                    new Length(['min' => 3]),
//                    new StsVersion(),
                ],
                'attr' => ['readonly' => true]
            ]);
        } else {
            $form->add('subversionSuffix', HiddenType::class, [
                'attr' => ['readonly' => true]
            ]);

            $subversion = false;

            foreach ($this->ecuSoftwareVersions as $ecuSoftwareVersion) {
                if ($ecuSoftwareVersion->getParentSwVersion() &&
                    $ecuSoftwareVersion->getParentSwVersion()->getEcuSwVersionId() == $header->getEcuSwVersion()) {
                    $subversion = true;

                    break;
                }
            }

            if ($subversion) {
                $form->add('swVersion', TextType::class, [
                    'attr' => ['readonly' => true]

                ]);
            } else {
                $form->add('swVersion', TextType::class, [
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                        /* DISABLE SW NAME VALIDATION RULES */
//                       new Length(['min' => 3]),
//                       new StsVersion(),
                    ]
                ]);
            }
        }
    }
}