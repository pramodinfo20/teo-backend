<?php

namespace App\Form\SubConfiguration;

use App\Form\SubConfiguration\Base\LongKeyBaseType;
use App\Model\LongKeyModel;
use App\Validator\Constraints\SubConfiguration\LongKey\InvalidLongKeyToFix;
use App\Validator\Constraints\SubConfiguration\LongKey\LongKey;
use App\Validator\Constraints\SubConfiguration\LongKey\LongKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\LongKey\TypeYearSeriesLongKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LongKeyFixType extends LongKeyBaseType
{
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
            'data_class' => LongKeyModel::class,
            /*
               * It's a global validation, with this case we can
               * set multiple fields to validate with their object such
               * like uds when need states of some another fields.
             */
            'constraints' => [
                new TypeYearSeriesLongKey(),
                new LongKey(),
                new InvalidLongKeyToFix(),
                new LongKeyQuestionMark(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}