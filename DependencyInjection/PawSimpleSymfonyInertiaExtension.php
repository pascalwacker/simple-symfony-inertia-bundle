<?php

namespace Paw\SimpleSymfonyInertiaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class PawSimpleSymfonyInertiaExtension extends ConfigurableExtension
{
    final protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $definition = $container->getDefinition('paw_simple_inertia.inertia');
        $definition->setArgument('$rootView', $mergedConfig['root_view']);
    }
}
