<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Preloader;

use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Model\ResourceInterface;

abstract class ResourcePreloader
{
    private array $resources = [];

    public function __construct(
        private RepositoryInterface $resourceRepository,
    ) {
    }

    abstract protected function validateResource(?ResourceInterface $resource): void;

    abstract protected function mapResults(array $resources): array;

    protected function getResourceByCode(string $code): ?ResourceInterface
    {
        if ([] === $this->resources) {
            $this->preload();
        }
        $code = mb_strtolower($code);
        if (!array_key_exists($code, $this->resources)) {
            return null;
        }

        return $this->resources[$code];
    }

    private function preload(): void
    {
        /** @var array<ResourceInterface> $resources */
        $resources = $this->resourceRepository->findAll();

        $this->resources = array_merge(
            ...$this->mapResults($resources),
        );
    }
}
