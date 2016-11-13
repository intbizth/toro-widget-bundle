<?php

namespace Toro\Bundle\WidgetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Toro\Bundle\WidgetBundle\Twig\ChannelAwareWidgetInterface;
use Toro\Bundle\WidgetBundle\Twig\ContainerAwareWidgetInterface;
use Toro\Bundle\WidgetBundle\Twig\WidgetInterface;

class RegisterWidgetPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $widgets = $container->getParameter('toro.widgets');

        foreach ($container->findTaggedServiceIds('twig.extension') as $key => $twigs) {
            $definition = $container->getDefinition($key);
            $class = $definition->getClass();

            if ($container->hasParameter($class)) {
                $class = $container->getParameter($class);
            }

            if (!class_exists($class)) {
                continue;
            }

            $reflex = new \ReflectionClass($class);

            if ($reflex->implementsInterface(WidgetInterface::class)) {
                $definition->addMethodCall('setRouter', [new Reference('router')]);

                if (isset($widgets[$class])) {
                    $definition->addMethodCall('setDefaultOptions', [$widgets[$class]['options']]);
                }
            }

            if ($reflex->implementsInterface(ContainerAwareWidgetInterface::class)) {
                $definition->addMethodCall('setContainer', [new Reference('service_container')]);
            }

            if ($reflex->implementsInterface(ChannelAwareWidgetInterface::class)) {
                $definition->addMethodCall('setChannelContext', [new Reference('sylius.context.channel')]);
            }
        }
    }
}
