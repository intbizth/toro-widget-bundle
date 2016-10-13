<?php

namespace Toro\Bundle\WidgetBundle\Twig;

class Demo2Widget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array &$options = [])
    {
        return [
            'awesome' => 'cool'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wg_demo2';
    }
}
