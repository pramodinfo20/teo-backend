<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OrderExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('orderForReservedParameter', [$this, 'orderForReservedParameter']),
        ];
    }

    /**
     * @param int $order
     *
     * @return string
     */
    public function orderForReservedParameter(int $order = 0)
    {
        return ($order == 0) ? null : $order;
    }
}