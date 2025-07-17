<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Preloader;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Resource\Model\ResourceInterface;
use Webmozart\Assert\Assert;

final class ChannelPreloader extends ResourcePreloader implements ChannelPreloaderInterface
{
    public function getChannelByCode(string $code): ?ChannelInterface
    {
        /** @var ?ChannelInterface $channel */
        $channel = $this->getResourceByCode($code);
        $this->validateResource($channel);

        return $channel;
    }

    protected function mapResults(array $resources): array
    {
        return array_map(function (ChannelInterface $channel) {
            $this->validateResource($channel);
            $code = $channel->getCode();
            Assert::notNull($code);

            return [mb_strtolower($code) => $channel];
        }, $resources);
    }

    protected function validateResource(?ResourceInterface $resource): void
    {
        if (null === $resource) {
            return;
        }

        Assert::isInstanceOf(
            $resource,
            ChannelInterface::class,
            sprintf(
                'Preloaded resource of class `%s` should be an instance of `%s`, instead `%s given.',
                self::class,
                ChannelInterface::class,
                get_class($resource),
            ),
        );
    }
}
