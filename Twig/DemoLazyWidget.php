<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoLazyWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'visibility' => 'onscreen',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array &$options = [])
    {
        return [
            'cool' => "I'm lazy!"
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wg_demo_lazy';
    }
}
