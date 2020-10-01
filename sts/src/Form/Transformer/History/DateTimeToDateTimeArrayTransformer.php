<?php

namespace App\Form\Transformer\History;

use Symfony\Component\Form\DataTransformerInterface;

class DateTimeToDateTimeArrayTransformer implements DataTransformerInterface
{
    public function transform($datetime)
    {
        if (!is_null($datetime)) {
            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i');

            $dateTimeString = $date . ' ' . $time;
        } else {
            $date = null;
            $time = null;

            $dateTimeString = 'null';
        }

        return $dateTimeString;
    }

    public function reverseTransform($datetimeString)
    {
        return \DateTime::createFromFormat('Y-m-d H:i', $datetimeString);
    }
}