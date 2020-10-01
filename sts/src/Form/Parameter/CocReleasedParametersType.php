<?php

namespace App\Form\Parameter;

use App\Form\EventListener\Parameters\FixCoCReleasedParametersListener;
use App\Entity\CocParameterRelease;
use App\Entity\Users;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CocReleasedParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('approvalCode', TextType::class)
            ->add('approvalDate', DateType::class, [
                'placeholder' => '----',
                'widget' => 'choice',
                'format' => 'yyyy-MM-dd',
//                'input' => 'date',
            ]);
//            ->add('releasedDate', HiddenType::class, [
////                'widget' => 'choice',
////                'format' => 'yyyy-MM-dd',
////                'input' => 'date',
//            ])
//            ->add('releasedBy', HiddenType::class);
//      , [
//                'class' => Users::class
//            ]);
//            ->add('releasedStatus', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults([
          'csrf_protection' => false,
          'data_class' => CocParameterRelease::class,
          'constraints' => [
          ]
      ]);
    }
}
