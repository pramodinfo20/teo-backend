<?php

namespace App\Form;

use App\Entity\EcuCommunicationProtocols;
use App\Entity\ReleaseStatus;
use App\Enum\OdxVersions;
use App\Form\EventListener\Parameters\Header\FixRequestListener;
use App\Form\EventListener\Parameters\Header\FixResponseListener;
use App\Form\EventListener\Parameters\Header\FixSuffixListener;
use App\Model\Header;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HeaderType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ecuId', HiddenType::class, [])
            ->add('stsVersion', HiddenType::class, [

            ])
            ->add('protocol', EntityType::class, [
                'class' => EcuCommunicationProtocols::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('p');
                },
            ])
            ->add('info')
            ->add('status', EntityType::class, [
                'class' => ReleaseStatus::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('v');
                }
            ])
            ->add('windchillUrl', TextType::class, [
            ])
            ->add('odxVersion', ChoiceType::class, [
                'choices' => [
                    OdxVersions::ODX_VERSION_1 => OdxVersions::ODX_VERSION_1,
                    OdxVersions::ODX_VERSION_2 => OdxVersions::ODX_VERSION_2
                ]
            ])
            ->add('bigEndian', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('ecu.sw.partials.twig.header.bigEndian', [], 'messages') => true,
                    $this->translator->trans('ecu.sw.partials.twig.header.littleEndian', [], 'messages') => false
                ]
            ])
            ->add('diagnosticIdentifier', TextType::class, [])
            ->addEventSubscriber(new FixSuffixListener($this->entityManager))
            ->addEventSubscriber(new FixRequestListener())
            ->addEventSubscriber(new FixResponseListener());;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Header::class,
        ]);
    }
}
