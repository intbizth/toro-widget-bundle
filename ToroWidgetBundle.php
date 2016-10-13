<?php

namespace Toro\Bundle\WidgetBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Toro\Bundle\WidgetBundle\DependencyInjection\Compiler\RegisterWidgetPass;

class ToroWidgetBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new RegisterWidgetPass());
    }
}
