<?php

namespace Toro\Bundle\WidgetBundle\Twig;

class DemoWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array &$options = [])
    {
        return [
            'cool' => 'awesome'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wg_demo';
    }
}
