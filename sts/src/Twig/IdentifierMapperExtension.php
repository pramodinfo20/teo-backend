<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IdentifierMapperExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('mapIdToName', [$this, 'mapIdToName']),
            new TwigFunction('mapNameToId', [$this, 'mapNameToId']),
        ];
    }

    public function mapIdToName(string $class, string $method, int $identifier): string
    {
        if (class_exists($class) && method_exists($class, $method)) {
            return call_user_func_array([$class, $method], [$identifier]);
        }
    }

    public function mapNameToId($class, $method, string $name): int
    {
        if (class_exists($class) && method_exists($class, $method)) {
            return call_user_func_array([$class, $method], [$name]);
        }
    }
}