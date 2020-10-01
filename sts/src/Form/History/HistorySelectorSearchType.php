<?php

namespace App\Form\History;

use App\Entity\HistoryEvents;
use App\Form\Transformer\History\DateTimeToDateTimeArrayTransformer;
use App\Model\History\Search\HistorySelector;
use App\Utils\Choice;
use App\Utils\Dictionary;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HistorySelectorSearchType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('createdFrom', ChoiceType::class, [
//                'choices' => Choice::transformDateTimeToChoice(
//                    $this->entityManager->getRepository($options['historyTable'])->findBy(['fk' => $options['fkId']]),
//                    'createdAt', true)
//
//            ])
//            ->add('createdTo', ChoiceType::class, [
//                'choices' => Choice::transformDateTimeToChoice(
//                    $this->entityManager->getRepository($options['historyTable'])->findBy(['fk' => $options['fkId']]),
//                    'createdAt', true)
//            ])
            ->add('createdFrom', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('createdTo', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('createdBy', ChoiceType::class, [
                'choices' => Choice::transformUserObjectToChoice(
                    $this->entityManager->getRepository($options['historyTable'])->findAll(),
                    'createdBy',  true)
            ])
            ->add('event', ChoiceType::class, [
                'choices' => Choice::transformToChoice(
                    $this->entityManager->getRepository(HistoryEvents::class)->findAll(),
                    'heId', 'eventName', true)
            ])
            ->add('comment', ChoiceType::class, [
                'choices' => Choice::transformToChoice(
                    $this->entityManager->getRepository($options['historyTable'])->findAll(),
                    'comment', 'comment',true)
            ])
            ->add('filters', ChoiceType::class, [
                'choices' => [ 0, 1, 2, 3, 4 ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('fk', HiddenType::class, []);

//            $builder->get('createdFrom')
//                ->addModelTransformer(new DateTimeToDateTimeArrayTransformer());
//            $builder->get('createdTo')
//                ->addModelTransformer(new DateTimeToDateTimeArrayTransformer());

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
            'historyTable' => null,
            'fkId' => null,
            'data_class' => HistorySelector::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'HistorySelectorSearchType';
    }
}