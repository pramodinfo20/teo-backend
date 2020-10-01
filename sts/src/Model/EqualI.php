<?php

namespace App\Model;


interface EqualI
{
    public function equals(EqualI $interface) : bool;
}