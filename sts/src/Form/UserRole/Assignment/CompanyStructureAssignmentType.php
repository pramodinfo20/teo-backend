<?php

namespace App\Form\UserRole\Assignment;

use App\Model\UserRole\Assignment\CompanyStructureAssignmentModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyStructureAssignmentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentRole', ChoiceType::class, [
                'choices' => $options['userRolesChoice'],
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('companyStructuresForCurrentRole', ChoiceType::class, [
                'choices' => $options['companyStructureChoice'],
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function ($val, $key, $index) use ($options) {
                     $depth = array_filter($options['companyStructureTree'], function ($structure) use ($val) {
                        return $structure['id'] == $val;
                    });

                     return [
                                'style' => 'margin-left: '.((int)reset($depth)['depth'] * 50).'px',
                                'class' => (!is_null(reset($depth)['user_role_id'])) ? 'assigned-structure' : '',
                                'disabled' => $options['customDisabled']
                     ];
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            /* It's important to set this due to new fields,
             * listeners and subscribers are not present at the
             * beginning of creation form and form validate extra
             * fields as a threat, do not turn it on when it's not necessary!
             */
            'allow_extra_fields' => true,
            'data_class' => CompanyStructureAssignmentModel::class,
            //custom options
            'userRolesChoice' => [],
            'companyStructureChoice' => [],
            'customDisabled' => false,
            'companyStructureTree' => []
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CompanyStructureAssignmentType';
    }
}