<?php

namespace App\Form\SubConfiguration;

use App\Form\SubConfiguration\Base\ShortKeyBaseType;
use App\Model\ShortKeyModel;
use App\Validator\Constraints\SubConfiguration\ShortKey\InvalidShortKey;
use App\Validator\Constraints\SubConfiguration\ShortKey\ShortKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\ShortKey\TypeYearSeriesShortKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShortKeyType extends ShortKeyBaseType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => ShortKeyModel::class,
            /*
              * It's a global validation, with this case we can
              * set multiple fields to validate with their object such
              * like uds when need states of some another fields.
             */
            'constraints' => [
                new InvalidShortKey(),
                new TypeYearSeriesShortKey(),
                new ShortKeyQuestionMark(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}