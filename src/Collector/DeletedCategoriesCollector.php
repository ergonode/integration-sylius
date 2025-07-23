<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Collector;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class DeletedCategoriesCollector implements DeletedCategoriesCollectorInterface
{
    private array $deletedCategories = [];

    private ?string $categoryDeletedStreamCursorEnd = null;

    public function __construct(
        private readonly CategoryApiClientInterface $categoryApiClient,
    ) {
    }

    public function collect(IntegrationInterface $integration): void
    {
        $deletedCategories = $this->categoryApiClient->getCategoryDeleted($integration, $this->categoryDeletedStreamCursorEnd);
        $stream = $deletedCategories['data']['categoryDeletedStream'];
        foreach ($this->retrieveCategoryCodesFromResponse($stream) as $code) {
            $this->deletedCategories[] = $code;
        }

        $this->categoryDeletedStreamCursorEnd = $stream['pageInfo']['endCursor'];

        if ($stream['pageInfo']['hasNextPage']) {
            $this->collect($integration);
        }
    }

    public function isInDeletedList(string $categoryCode): bool
    {
        return in_array($categoryCode, $this->deletedCategories, true);
    }

    public function getDeletedList(): array
    {
        return $this->deletedCategories;
    }

    public function getCursorEnd(): ?string
    {
        return $this->categoryDeletedStreamCursorEnd;
    }

    private function retrieveCategoryCodesFromResponse(array $stream): array
    {
        return array_map(fn (array $category) => $category['node'], $stream['edges']);
    }
}
