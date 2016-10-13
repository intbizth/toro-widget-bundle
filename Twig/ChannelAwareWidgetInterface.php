<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Sylius\Component\Channel\Context\ChannelContextInterface;

interface ChannelAwareWidgetInterface
{
    /**
     * @param ChannelContextInterface $channelContext
     */
    public function setChannelContext(ChannelContextInterface $channelContext);
}
