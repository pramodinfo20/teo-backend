<?php

namespace App\Form\EcuSwProperties;

use App\Model\EcuSwProperties\EcuSwPropertiesModel;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcuSwPropertiesModelType extends AbstractType
{

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * EcuSwPropertiesModelType constructor.
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->translator = $translator;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('name', TextType::class, [
                'attr' => [],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('value', TextType::class, [
                'attr' => [],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('order', IntegerType::class, [
                'attr' => [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => EcuSwPropertiesModel::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'EcuSwPropertiesModelType';
    }


}