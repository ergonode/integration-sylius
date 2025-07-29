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
use BitBag\SyliusErgonodePlugin\Converter\MoneyConverter;
use BitBag\SyliusErgonodePlugin\Converter\MoneyConverterInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class PriceValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly ProductVariantResolverInterface $productVariantResolver,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly  MoneyConverterInterface $moneyConverter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ?ResourceRelationMappingInterface $mapping): void
    {
        if (null === $mapping || null === $mapping->getChannelCode()) {
            $this->logger->warning('To assign price, you need to add mapping with channel code specified');

            return;
        }

        $value = array_shift($values);
        $variant = $this->productVariantResolver->getVariant($resource);
        $channelCode = $mapping->getChannelCode();

        $channel = $this->channelRepository->findOneByCode($channelCode);
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        if ($variant->hasChannelPricingForChannel($channel)) {
            /** @var ChannelPricingInterface $pricing */
            $pricing = $variant->getChannelPricingForChannel($channel);
        } else {
            /** @var ChannelPricingInterface $pricing */
            $pricing = $this->channelPricingFactory->createNew();
            $pricing->setChannelCode($channelCode);
            $variant->addChannelPricing($pricing);
        }

        $relationType = $mapping->getRelationType();
        Assert::notNull($mapping->getSyliusValue());
        $value = $this->moneyConverter->convert($value);
        if (RelationType::PRODUCT_PROPERTY === $relationType) {
            $property = ProductPropertyMapping::tryFrom($mapping->getSyliusValue());
            switch ($property) {
                case ProductPropertyMapping::PRICE:
                    $pricing->setPrice($value);

                    return;
                case ProductPropertyMapping::MINIMUM_PRICE:
                    $pricing->setMinimumPrice($value);

                    return;
                case ProductPropertyMapping::ORIGINAL_PRICE:
                    $pricing->setOriginalPrice($value);

                    return;
            }
        }

        if (RelationType::VARIANT_PROPERTY === $relationType) {
            $property = ProductVariantPropertyMapping::tryFrom($mapping->getSyliusValue());
            switch ($property) {
                case ProductVariantPropertyMapping::PRICE:
                    $pricing->setPrice($value);

                    return;
                case ProductVariantPropertyMapping::MINIMUM_PRICE:
                    $pricing->setMinimumPrice($value);

                    return;
                case ProductVariantPropertyMapping::ORIGINAL_PRICE:
                    $pricing->setOriginalPrice($value);

                    return;
            }
        }

        throw SynchronizationException::invalidRelationType($relationType);
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        $isVariant = $resource instanceof ProductVariantInterface;
        $productWithSingleVariant = $resource instanceof ProductInterface && 1 == $resource->getVariants()->count();

        if (!$isVariant && !$productWithSingleVariant) {
            return false;
        }
        $productProperty = ProductPropertyMapping::tryFrom($property);
        $variantProperty = ProductVariantPropertyMapping::tryFrom($property);

        return
            in_array($productProperty, [
                    ProductPropertyMapping::PRICE,
                    ProductPropertyMapping::ORIGINAL_PRICE,
                    ProductPropertyMapping::MINIMUM_PRICE,
                ], true) ||
            in_array($variantProperty, [
                ProductVariantPropertyMapping::PRICE,
                ProductVariantPropertyMapping::ORIGINAL_PRICE,
                ProductPropertyMapping::MINIMUM_PRICE,
            ], true)
        ;
    }
}
