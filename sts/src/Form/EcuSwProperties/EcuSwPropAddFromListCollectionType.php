<?php


namespace App\Form\EcuSwProperties;


use App\Model\EcuSwProperties\EcuSwPropertiesCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcuSwPropAddFromListCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => EcuSwPropertiesCollection::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('properties', CollectionType::class, [
                'entry_type' => EcuSwPropAddFromListModelType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'attr' => [
                    'class' => 'properties-table'
                ],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'EcuSwPropAddFromListCollectionType';
    }

}