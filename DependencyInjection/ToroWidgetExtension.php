<?php

namespace Toro\Bundle\WidgetBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ToroWidgetExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('toro.widgets', $config['widgets']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // auto register
        // simple widget without register twig service
        // can set or not set the service, if set can with or without `twig.extension` tag
        foreach (array_keys($config['widgets']) as $class) {
            $def = new Definition();
            $hasDefinition = false;

            foreach ($container->getDefinitions() as $definition) {
                if ($definition->getClass() === $class) {
                    $def = $definition;
                    $hasDefinition = true;
                    break;
                }
            }

            if (!$hasDefinition) {
                $container->setDefinition(strtolower(str_replace('\\', '_', $class)), $def);
            }

            $def->setClass($class)->addTag('twig.extension')->setLazy(true);
        }
    }
}
