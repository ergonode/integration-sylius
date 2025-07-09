<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Property;

use BitBag\SyliusErgonodePlugin\Downloader\ErgonodeFileDownloaderInterface;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ImagesAwareInterface;

abstract class MediaAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly ErgonodeFileDownloaderInterface $downloader,
    ) {
    }

    public function saveImage(ImagesAwareInterface|ImageAwareInterface $resource, array $values, ImageInterface $image): void
    {
        $this->downloader->download($values, $image);

        if ($resource instanceof ImageAwareInterface) {
            $resource->setImage($image);

            return;
        }

        $resource->addImage($image);
    }
}
