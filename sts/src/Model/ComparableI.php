<?php

namespace App\Model;

interface ComparableI
{
    /** Like a Spaceship - <=> - 0 - equal, -1 - left < right, 1 left > right
     *
     * @param ComparableI $interface
     *
     * @return int
     */
    public function compare(ComparableI $interface) : int;
}