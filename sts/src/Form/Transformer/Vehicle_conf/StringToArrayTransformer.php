<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 24.10.19
 * Time: 14:03
 */
namespace App\Form\Transformer\Vehicle_conf;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StringToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms an array to a string.
     * GET FIRST ELEMENT -> POSSIBLE LOSS OF DATA
     *
     * @return string
     */
    public function transform($array)
    {
        if (!empty($array))  return $array[0];
        else return null;
    }

    /**
     * Transforms a string to an array.
     *
     * @param  string $string
     *
     * @return array
     */
    public function reverseTransform($string)
    {
        return array($string);
    }
}