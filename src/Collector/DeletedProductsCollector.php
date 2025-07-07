<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Collector;

use BitBag\SyliusErgonodePlugin\API\Client\AttributeApiClientInterface;
use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class DeletedProductsCollector implements DeletedProductsCollectorInterface
{
    private array $deletedProducts = [];

    private ?string $productDeletedStreamCursorEnd = null;

    public function __construct(
        private readonly ProductApiClientInterface $productApiClient,
    ) {
    }

    public function collect(IntegrationInterface $integration): void
    {
        $deletedAttributes = $this->productApiClient->getDeletedProducts();
        $stream = $deletedAttributes['data']['productDeletedStream'];
        foreach ($this->retrieveProductSkusFromResponse($stream) as $code) {
            $this->deletedProducts[] = $code;
        }

        $this->productDeletedStreamCursorEnd = $stream['pageInfo']['endCursor'];

        if ($stream['pageInfo']['hasNextPage']) {
            $this->collect($integration);
        }
    }

    public function isInDeletedList(string $productSku): bool
    {
        return in_array($productSku, $this->deletedProducts, true);
    }

    public function getDeletedList(): array
    {
        return $this->deletedProducts;
    }

    public function getCursorEnd(): ?string
    {
        return $this->productDeletedStreamCursorEnd;
    }

    private function retrieveProductSkusFromResponse(array $stream): array
    {
        return array_map(fn (array $attribute) => $attribute['node'], $stream['edges']);
    }
}
