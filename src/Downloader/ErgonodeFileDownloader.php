<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Downloader;

use BitBag\SyliusErgonodePlugin\Generator\DownloadedFilePathGeneratorInterface;
use Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ErgonodeFileDownloader implements ErgonodeFileDownloaderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private FilesystemAdapterInterface $filesystem,
        private DownloadedFilePathGeneratorInterface $downloadedFilePathGenerator,
    ) {
    }

    public function download(array $fileInfo, ImageInterface $image): void
    {
        $url = $fileInfo['url'];
        $filename = $fileInfo['name'];

        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('Failed to download file: %s', $response->getStatusCode()));
        }

        $content = $response->getContent();

        if (null === $path = $image->getPath()) {
            $path = $this->downloadedFilePathGenerator->generate($filename);
        }

        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }

        $this->filesystem->write($path, $content);
        $image->setPath($path);
    }
}
