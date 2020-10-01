<?php

namespace App\Form\SubConfiguration;

use App\Form\SubConfiguration\Base\LongKeyBaseType;
use App\Model\LongKeyModel;
use App\Validator\Constraints\SubConfiguration\LongKey\InvalidLongKey;
use App\Validator\Constraints\SubConfiguration\LongKey\LongKey;
use App\Validator\Constraints\SubConfiguration\LongKey\LongKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\LongKey\TypeYearSeriesLongKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LongKeyType extends LongKeyBaseType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => LongKeyModel::class,
            /*
               * It's a global validation, with this case we can
               * set multiple fields to validate with their object such
               * like uds when need states of some another fields.
             */
            'constraints' => [
                new TypeYearSeriesLongKey(),
                new LongKey(),
                new InvalidLongKey(),
                new LongKeyQuestionMark(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}