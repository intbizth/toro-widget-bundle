<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoAutoRefreshWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'auto_refresh' => true,
            'auto_refresh_timer' => 5000,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array &$options = [])
    {
        return [
            'cool' => "I'm auto refresh!"
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wg_demo_auto_refresh';
    }
}
