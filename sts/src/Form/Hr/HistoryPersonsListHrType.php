<?php

namespace App\Form\Hr;


use App\Entity\HistoryPersonsListHr;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoryPersonsListHrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createdAt', EntityType::class, array(
                'class' => 'App:HistoryPersonsListHr',
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.createdAt', 'DESC');
                },
                'data' => $options['history']
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => HistoryPersonsListHr::class,
            'constraints' => [
            ],
            'history' => null
        ]);
    }

}