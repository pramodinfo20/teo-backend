<?php


namespace App\Form\EcuSwProperties;


use App\Form\EventListener\EcuSwProperties\AddIsAssignedFieldSubscriber;
use App\Model\EcuSwProperties\EcuSwPropertiesModel;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcuSwPropAddFromListModelType extends AbstractType
{
    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * EcuSwPropAddFromListModelType constructor.
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => EcuSwPropertiesModel::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
//            ->add('isAssigned', CheckboxType::class)
            ->add('name', HiddenType::class)
            ->add('value', HiddenType::class);

        $builder->addEventSubscriber(new AddIsAssignedFieldSubscriber());
    }

    public function getBlockPrefix()
    {
        return 'EcuSwPropAddFromListModelType';
    }


}