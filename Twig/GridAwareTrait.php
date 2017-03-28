<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataProvider;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

trait GridAwareTrait
{
    use ContainerAwareTrait;

    /**
     * @return DataProvider
     */
    private function getGridDataProvider()
    {
        return $this->container->get('sylius.grid.data_provider');
    }

    /**
     * @param string $grid
     *
     * @return Grid
     */
    private function getGridProvider($grid)
    {
        return $this->container->get('sylius.grid.provider')->get($grid);
    }

    /**
     * @param array $options
     *
     * @return Pagerfanta
     */
    protected function createGrid(array $options)
    {
        $parameters = new Parameters($options);

        $grid = $this->getGridProvider($options['grid']);
        $grid->setDriverConfiguration(array_replace_recursive($grid->getDriverConfiguration(), [
            'repository' => ['arguments' => [$options['criteria']]],
        ]));

        /** @var Pagerfanta $pager */
        $pager = $this->getGridDataProvider()->getData($grid, $parameters);

        $pager->setMaxPerPage($options['limit']);
        $pager->setCurrentPage($options['page']);

        return $pager;
    }

    /**
     * {@inheritdoc}
     */
    protected function runtimeOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'heading' => null,
            'criteria' => [],
            'limit' => 5,
            'page' => 1,
        ]);

        $resolver->setRequired('grid');
        $resolver->setAllowedTypes('criteria', ['array']);
        $resolver->setAllowedTypes('heading', ['string', 'null']);
        $resolver->setAllowedTypes('grid', ['string']);
        $resolver->setAllowedTypes('limit', ['integer']);

        $resolver->setNormalizer('limit', function (Options $options, $value) {
            if ($value > 20) {
                return 20;
            }

            return $value;
        });
    }
}
