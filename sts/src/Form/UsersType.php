<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])
                ]
            ])
            ->add('divisionId')
            ->add('email')
            ->add('passwd')
            ->add('privileges')
            ->add('fname')
            ->add('lname')
            ->add('addedby')
            ->add('role')
            ->add('zsplId')
            ->add('notifications')
            ->add('cookiesAccepted')
            ->add('privacyAccepted')
            ->add('timestampLastLoggedIn')
            ->add('ipLastLoggedIn')
            ->add('lastSessionId')
            ->add('workshop');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
