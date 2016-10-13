<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Sylius\Component\Channel\Context\ChannelContextInterface;

trait ChannelContextTrait
{
    /**
     * @var ChannelContextInterface
     */
    protected $channelContext;

    /**
     * @param ChannelContextInterface $channelContext
     */
    public function setChannelContext(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }
}
