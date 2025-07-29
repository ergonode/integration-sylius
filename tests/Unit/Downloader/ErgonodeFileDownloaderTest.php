<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Downloader;

use BitBag\SyliusErgonodePlugin\Downloader\ErgonodeFileDownloader;
use BitBag\SyliusErgonodePlugin\Generator\DownloadedFilePathGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ErgonodeFileDownloaderTest extends TestCase
{
    public function testSuccessfulDownloadAndWrite(): void
    {
        $fileInfo = [
            'url' => 'https://cdn.example.com/image.jpg',
            'name' => 'image.jpg',
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn('file-content');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', $fileInfo['url'])
            ->willReturn($response);

        $filesystem = $this->createMock(FilesystemAdapterInterface::class);
        $filesystem->method('has')->with('media/image.jpg')->willReturn(true);
        $filesystem->expects(self::once())->method('delete')->with('media/image.jpg');
        $filesystem->expects(self::once())->method('write')->with('media/image.jpg', 'file-content');

        $pathGenerator = $this->createMock(DownloadedFilePathGeneratorInterface::class);
        $pathGenerator->method('generate')->with('image.jpg')->willReturn('media/image.jpg');

        $image = $this->createMock(ImageInterface::class);
        $image->expects(self::once())->method('setPath')->with('media/image.jpg');

        $downloader = new ErgonodeFileDownloader($httpClient, $filesystem, $pathGenerator);
        $downloader->download($fileInfo, $image);
    }

    public function testThrowsExceptionOnRequestFailed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to download file: 404');

        $fileInfo = [
            'url' => 'https://cdn.example.com/missing.jpg',
            'name' => 'missing.jpg',
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $filesystem = $this->createMock(FilesystemAdapterInterface::class);
        $pathGenerator = $this->createMock(DownloadedFilePathGeneratorInterface::class);
        $image = $this->createMock(ImageInterface::class);

        $downloader = new ErgonodeFileDownloader($httpClient, $filesystem, $pathGenerator);
        $downloader->download($fileInfo, $image);
    }
}
