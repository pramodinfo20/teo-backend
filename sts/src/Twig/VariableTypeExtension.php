<?php


namespace App\Twig;

use App\Enum\Entity\VariableTypes;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VariableTypeExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('getVariableTypeByName', [$this, 'getVariableTypeByName']),
        ];
    }

    /**
     * @param string $variableType
     *
     * @return int|null
     */
    public function getVariableTypeByName(string $variableType): ?int
    {
        return VariableTypes::getVariableTypeByName($variableType);
    }
}