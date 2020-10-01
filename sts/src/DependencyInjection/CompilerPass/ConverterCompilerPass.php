<?php

namespace App\DependencyInjection\CompilerPass;

use App\Converter\Convertible;
use App\Utils\HexConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConverterCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $resolver = $container->findDefinition(HexConverter::class);
        $strategies = array_keys($container->findTaggedServiceIds(Convertible::SERVICE_TAG));

        foreach ($strategies as $strategy) {
            $resolver->addMethodCall('addStrategy', [new Reference($strategy)]);
        }
    }
}