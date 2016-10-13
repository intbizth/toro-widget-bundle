<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Symfony\Component\Routing\RouterInterface;
use Toro\Bundle\AdminBundle\Model\ChannelInterface;
use Toro\Bundle\AdminBundle\Model\TaxonInterface;

abstract class AbstractChannelTaxonBaseWidget extends AbstractWidget
{
    /**
     * @return TaxonRepository
     */
    final protected function getTaxonRepository()
    {
        return $this->container->get('sylius.repository.taxon');
    }

    /**
     * @return ChannelInterface
     */
    final protected function getCurrentChannel()
    {
        return $this->container->get('sylius.context.channel')->getChannel();
    }

    /**
     * @param null|string $taxonCode
     * @return TaxonInterface
     */
    final protected function getTaxon($taxonCode = null)
    {
        return $taxonCode ? $this->getTaxonRepository()->findOneByCode($taxonCode) : $this->getCurrentChannel()->getTaxons()->first();
    }
}
