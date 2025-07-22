<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\Product\ChannelsValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\ProductPropertyMapping;
use BitBag\SyliusErgonodePlugin\Preloader\ChannelPreloaderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\ProductResourceInterface;
use Tests\BitBag\SyliusErgonodePlugin\Unit\VariantResourceInterface;

final class ChannelsValueAssignerTest extends TestCase
{
    private ChannelPreloaderInterface $channelPreloader;
    private LoggerInterface $logger;
    private ChannelsValueAssigner $channelsValueAssigner;

    protected function setUp(): void
    {
        $this->channelPreloader = $this->createMock(ChannelPreloaderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->channelsValueAssigner = new ChannelsValueAssigner(
            $this->channelPreloader,
            $this->logger
        );
    }

    public function testItAssigsChannelToProduct(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $channel1 = $this->createMock(ChannelInterface::class);
        $channel2 = $this->createMock(ChannelInterface::class);
        $values = [['web', 'mobile']];

        $product
            ->method('getChannels')
            ->willReturn(new ArrayCollection());

        $this->channelPreloader
            ->expects(self::exactly(2))
            ->method('getChannelByCode')
            ->willReturnOnConsecutiveCalls($channel1, $channel2);

        $product
            ->expects(self::exactly(2))
            ->method('addChannel')
        ;

        $this->logger
            ->expects(self::never())
            ->method('warning');

        $this->channelsValueAssigner->assign($product, $values, $mapping);
    }

    public function test_it_removes_existing_channels_not_in_new_assignment(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $newChannel = $this->createMock(ChannelInterface::class);
        $existingChannel = $this->createMock(ChannelInterface::class);
        $values = [['web']];

        $product
            ->method('getChannels')
            ->willReturn(new ArrayCollection([$existingChannel, $newChannel]));

        $this->channelPreloader
            ->expects(self::once())
            ->method('getChannelByCode')
            ->with('web')
            ->willReturn($newChannel);

        $product
            ->expects(self::once())
            ->method('addChannel')
            ->with($newChannel);

        $product
            ->expects(self::once())
            ->method('removeChannel')
            ->with($existingChannel);

        $this->channelsValueAssigner->assign($product, $values, $mapping);
    }

    public function test_it_logs_warning_when_channel_not_found(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $values = [['nonexistent']];

        $product
            ->method('getChannels')
            ->willReturn(new ArrayCollection());

        $this->channelPreloader
            ->expects(self::once())
            ->method('getChannelByCode')
            ->with('nonexistent')
            ->willReturn(null);

        $product
            ->expects(self::never())
            ->method('addChannel');

        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with('Channel with code "nonexistent" not found.');

        $this->channelsValueAssigner->assign($product, $values, $mapping);
    }

    public function test_it_handles_mixed_found_and_not_found_channels(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $foundChannel = $this->createMock(ChannelInterface::class);
        $values = [['web', 'nonexistent', 'mobile']];

        $product
            ->method('getChannels')
            ->willReturn(new ArrayCollection());

        $this->channelPreloader
            ->expects(self::exactly(3))
            ->method('getChannelByCode')
            ->willReturnOnConsecutiveCalls($foundChannel, null, null);

        $product
            ->expects(self::once())
            ->method('addChannel')
            ->with($foundChannel)
        ;

        $this->logger
            ->expects(self::exactly(2))
            ->method('warning')
        ;

        $this->channelsValueAssigner->assign($product, $values, $mapping);
    }

    public function test_it_handles_empty_channel_codes_array(): void
    {
        $product = $this->createMock(ProductResourceInterface::class);
        $mapping = $this->createMock(ResourceRelationMappingInterface::class);
        $existingChannel = $this->createMock(ChannelInterface::class);
        $values = [[]];

        $product
            ->method('getChannels')
            ->willReturn(new ArrayCollection([$existingChannel]));

        $this->channelPreloader
            ->expects(self::never())
            ->method('getChannelByCode');

        $product
            ->expects(self::never())
            ->method('addChannel');

        $product
            ->expects(self::once())
            ->method('removeChannel')
            ->with($existingChannel);

        $this->channelsValueAssigner->assign($product, $values, $mapping);
    }

    /**
     * @dataProvider supportProductDataProvider
     */
    public function testItSupportsCorrectProductAndProperty(
        string $property,
        bool $expected
    ): void {
        $product = $this->createMock(ProductResourceInterface::class);
        $result = $this->channelsValueAssigner->supports($product, $property);

        self::assertSame($expected, $result);
    }

    /**
     * @dataProvider supportVariantDataProvider
     */
    public function testItSupportsCorrectVariantAndProperty(
        string $property,
        bool $expected
    ): void {
        $resource = $this->createMock(VariantResourceInterface::class);
        $result = $this->channelsValueAssigner->supports($resource, $property);

        self::assertSame($expected, $result);
    }

    public static function supportProductDataProvider(): array
    {

        return [
            'product with channels property' => [
                ProductPropertyMapping::CHANNELS->value,
                true,
            ],
            'product with non-channels property' => [
                ProductPropertyMapping::ENABLED->value,
                false,
            ],
        ];
    }

    public static function supportVariantDataProvider(): array
    {

        return [
            'variant with channels property' => [
                ProductPropertyMapping::CHANNELS->value,
                false,
            ],
            'variant with non-channels property' => [
                ProductPropertyMapping::ENABLED->value,
                false,
            ],
        ];
    }
}
