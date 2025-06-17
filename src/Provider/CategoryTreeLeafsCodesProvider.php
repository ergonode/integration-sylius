<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;

final readonly class CategoryTreeLeafsCodesProvider implements CategoryTreeLeafsCodesProviderInterface
{
    public function __construct(
        private CategoryApiClientInterface $categoryApiClient,
    ) {
    }

    /** @inheritdoc  */
    public function provide(IntegrationInterface $integration): array
    {
        $response = $this->categoryApiClient->getCategoryTreeLeafCodes($integration);
        $categoryTree = $response['data']['categoryTree'];
        $categoryTreeCode = $categoryTree['code'];

        if ($categoryTreeCode !== $integration->getCategoryTreeCode()) {
            throw CategorySynchronizationException::invalidCategoryTreeFound(
                $integration->getCategoryTreeCode(),
                $categoryTree['code'],
            );
        }
        $categoryEdges = $categoryTree['categoryTreeLeafList']['edges'];

        return [
            'codes' => array_merge(
                ...array_map(fn (array $edge) => [
                    $edge['node']['category']['code'],
                ], $categoryEdges),
            ),
            'parents' => array_merge(
                ...array_map(fn (array $edge) => [
                    $edge['node']['category']['code'] => $edge['node']['parentCategory']['code'],
                ], array_filter($categoryEdges, fn (array $edge) => null !== $edge['node']['parentCategory'])),
            ),
        ];
    }
}
