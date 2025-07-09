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
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class MediaAssigner extends PropertyMediaAssigner
{
    public function __construct(
        private readonly FactoryInterface $productImageFactory,
        private readonly LoggerInterface $logger,
        ErgonodeFileDownloaderInterface $downloader,
    ) {
        parent::__construct($downloader);
    }

    public function assign(ResourceInterface $resource, array $values, ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        $owner = $resource instanceof ProductVariantInterface ? $resource->getProduct() : $resource;
        if (!$owner instanceof ImagesAwareInterface && !$owner instanceof ImageAwareInterface) {
            $ownerFound = $owner instanceof ResourceInterface;
            $this->logger->error('Owner of media is not ImagesAwareInterface or ImageAwareInterface', [
                'ownerId' => $ownerFound ? $owner->getId() : $resource->getId(),
                'ownerClass' => $ownerFound ? get_class($owner) : get_class($resource),
            ]);

            return;
        }

        if (null === $image = $this->findImage($resource, $value[0])) {
            /** @var ProductImageInterface $image */
            $image = $this->productImageFactory->createNew();
        }

        $this->saveImage($owner, $value[0], $image);

        if ($resource instanceof ProductVariantInterface) {
            $image->addProductVariant($resource);
        }
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        if (!$resource instanceof ProductInterface && !$resource instanceof ProductVariantInterface) {
            return false;
        }

        return ProductPropertyMapping::MEDIA === ProductPropertyMapping::tryFrom($property) ||
            ProductVariantPropertyMapping::MEDIA === ProductVariantPropertyMapping::tryFrom($property)
        ;
    }

    private function findImage(ResourceInterface $resource, mixed $value): ?ImageInterface
    {
        if ($resource instanceof ProductVariantInterface) {
            $variant = $resource;
            $product = $variant->getProduct();

            if (!$product instanceof ProductInterface) {
                return null;
            }

            $image = $product->getImages()->filter(
                static function (ProductImageInterface $image) use ($value, $variant) {
                    if (!$image->hasProductVariant($variant)) {
                        return false;
                    }
                    $path = $image->getPath();
                    $pathParts = explode('/', $path);
                    return end($pathParts) === $value['name'];
                }
            )->first();

            return $image instanceof ImageInterface ? $image : null;
        }

        if ($resource instanceof ImageAwareInterface) {
            $image = $resource->getImage();
            if (null === $image) {
                return null;
            }

            $path = $image->getPath();
            $pathParts = explode('/', $path);
            return end($pathParts) === $value['name'] ? $image : null;
        }

        if ($resource instanceof ImagesAwareInterface) {
            $image = $resource->getImages()->filter(
                static function (ImageInterface $image) use ($value) {
                    $path = $image->getPath();
                    $pathParts = explode('/', $path);
                    return end($pathParts) === $value['name'];
                }
            )->first();

            return $image instanceof ImageInterface ? $image : null;
        }

        return null;
    }
}
