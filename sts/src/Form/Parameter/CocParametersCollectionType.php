<?php


namespace App\Form\Parameter;

use App\Model\Parameter\CocCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CocParametersCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameters', CollectionType::class, [
                'entry_type' => CocParametersValuesType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'attr' => [
                    'class' => 'coc-table',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => CocCollection::class,
            'constraints' => [
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CocParametersCollectionType';
    }
}
