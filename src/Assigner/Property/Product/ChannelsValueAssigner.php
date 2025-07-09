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
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class ChannelsValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        Assert::isInstanceOf($resource, ProductInterface::class);
        $channels = $this->channelRepository->findBy(['code' => $value]);

        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $resource->addChannel($channel);
        }
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        return $resource instanceof ProductInterface &&
            ProductPropertyMapping::CHANNELS === ProductPropertyMapping::tryFrom($property)
        ;
    }
}
