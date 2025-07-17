<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Preloader\ChannelPreloaderInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class ChannelsValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly ChannelPreloaderInterface $channelPreloader,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ?ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        Assert::isInstanceOf($resource, ProductInterface::class);
        $channels = [];
        foreach ($value as $code) {
            Assert::string($code);
            /** @var ?ChannelInterface $channel */
            $channel = $this->channelPreloader->getChannelByCode($code);

            if ($channel instanceof ChannelInterface) {
                $resource->addChannel($channel);
                $channels[] = $channel;

                continue;
            }

            $this->logger->warning(sprintf('Channel with code "%s" not found.', $code));
        }

        foreach ($resource->getChannels() as $channel) {
            if (!in_array($channel, $channels, true)) {
                $resource->removeChannel($channel);
            }
        }
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        return $resource instanceof ProductInterface &&
            ProductPropertyMapping::CHANNELS === ProductPropertyMapping::tryFrom($property)
        ;
    }
}
