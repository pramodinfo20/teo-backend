<?php

namespace App\Form\SubConfiguration;

use App\Form\SubConfiguration\Base\ShortKeyBaseType;
use App\Model\ShortKeyModel;
use App\Validator\Constraints\SubConfiguration\ShortKey\ShortKey;
use App\Validator\Constraints\SubConfiguration\ShortKey\ShortKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\ShortKey\TypeYearSeriesShortKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShortKeyFixType extends ShortKeyBaseType
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
            'data_class' => ShortKeyModel::class,
            /*
              * It's a global validation, with this case we can
              * set multiple fields to validate with their object such
              * like uds when need states of some another fields.
             */
            'constraints' => [
                new TypeYearSeriesShortKey(),
                new ShortKey(),
                new ShortKeyQuestionMark(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}