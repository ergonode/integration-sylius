<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property\Common;

use BitBag\SyliusErgonodePlugin\Assigner\Property\MediaAssigner as PropertyMediaAssigner;
use BitBag\SyliusErgonodePlugin\Downloader\ErgonodeFileDownloaderInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Enum\ProductVariantPropertyMapping;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class MediaAssigner extends PropertyMediaAssigner
{
    public function __construct(
        private readonly FactoryInterface $productImageFactory,
        ErgonodeFileDownloaderInterface $downloader,
    ) {
        parent::__construct($downloader);
    }

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        Assert::isInstanceOf($resource, ImagesAwareInterface::class);
        /** @var ProductImageInterface $image */
        $image = $this->productImageFactory->createNew();
        $owner = $resource instanceof ProductVariantInterface ? $resource->getProduct() : $resource;
        Assert::notNull($owner);
        $this->saveImage($owner, $values, $image);

        if ($resource instanceof ProductVariantInterface) {
            $image->addProductVariant($resource);
        }
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        if (!$resource instanceof ProductInterface || !$resource instanceof ProductVariantInterface) {
            return true;
        }

        return ProductPropertyMapping::MEDIA === ProductPropertyMapping::tryFrom($property)
            || ProductVariantPropertyMapping::MEDIA === ProductVariantPropertyMapping::tryFrom($property)
        ;
    }
}
