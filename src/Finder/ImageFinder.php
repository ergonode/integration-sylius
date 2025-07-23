<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Finder;

use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class ImageFinder implements ImageFinderInterface
{
    public function findImage(ResourceInterface $resource, mixed $value): ?ImageInterface
    {
        if ($resource instanceof ProductVariantInterface) {
            return $this->findForProductVariant($resource, $value);
        }

        if ($resource instanceof ImageAwareInterface) {
            return $this->findForImageAwareSubject($resource, $value['name']);
        }

        if ($resource instanceof ImagesAwareInterface) {
            return $this->findForImagesAwareSubject($resource, $value);
        }

        return null;
    }

    private function findForProductVariant(ProductVariantInterface $resource, mixed $value): ?ImageInterface
    {
        $variant = $resource;
        $product = $variant->getProduct();

        if (!$product instanceof ProductInterface) {
            return null;
        }

        $image = $product->getImages()->filter(
            static function (ImageInterface $image) use ($value, $variant) {
                /** @var ProductImageInterface $image */
                if (!$image->hasProductVariant($variant)) {
                    return false;
                }

                if (null === $path = $image->getPath()) {
                    return false;
                }
                $pathParts = explode('/', $path);

                return end($pathParts) === $value['name'];
            },
        )->first();

        return $image instanceof ImageInterface ? $image : null;
    }

    private function findForImageAwareSubject(ImageAwareInterface $resource, string $name): ?ImageInterface
    {
        $image = $resource->getImage();
        if (null === $image || null === $path = $image->getPath()) {
            return null;
        }

        $pathParts = explode('/', $path);

        return end($pathParts) === $name ? $image : null;
    }

    private function findForImagesAwareSubject(ImagesAwareInterface $resource, mixed $value): ?ImageInterface
    {
        $image = $resource->getImages()->filter(
            static function (ImageInterface $image) use ($value) {
                if (null === $path = $image->getPath()) {
                    return false;
                }

                $pathParts = explode('/', $path);

                return end($pathParts) === $value['name'];
            },
        )->first();

        return $image instanceof ImageInterface ? $image : null;
    }
}
