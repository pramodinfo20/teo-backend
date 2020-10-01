<?php

namespace App\Form\Configuration;

use App\Entity\OdxSourceTypes;
use App\Model\Configuration\Header;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HeaderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('odxSourceType', EntityType::class, [
                'class' => OdxSourceTypes::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('p');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Header::class,
        ]);
    }
}
