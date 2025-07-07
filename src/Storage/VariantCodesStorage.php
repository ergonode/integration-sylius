<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use BitBag\SyliusErgonodePlugin\API\Client\VariantApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;

final class VariantCodesStorage implements VariantCodesStorageInterface
{
    private array $variantsLists = [];

    private array $variantPayloads = [];

    public function __construct(
        private readonly VariantApiClientInterface $apiClient,
    ) {
    }

    public function initialize(IntegrationInterface $integration): void
    {
        $cursor = null;
        $hasNextPage = true;

        while ($hasNextPage) {
            $response = $this->apiClient->getVariants($integration, $cursor);
            if (!array_key_exists('data', $response) &&
                !array_key_exists('productStream', $response['data'])
            ) {
                throw SynchronizationException::invalidArrayStructure($response, 'data.productStream');
            }
            $pageInfo = $response['data']['productStream']['pageInfo'];
            $edges = $response['data']['productStream']['edges'];
            foreach ($edges as $edge) {
                $edge = $edge['node'];
                if (!array_key_exists('sku', $edge)) {
                    continue;
                }
                $sku = $edge['sku'];
                $variantSkus = [];
                foreach ($edge['variantList']['edges'] as $variant) {
                    $variantNode = $variant['node'];

                    if (!array_key_exists('sku', $variantNode) ||
                    !array_key_exists('__typename', $variantNode) ||
                    'SimpleProduct' !== $variantNode['__typename']
                    ) {
                        continue;
                    }

                    $variantSkus[] = $variantNode['sku'];
                }

                $this->variantsLists[$sku] = [
                    'options' => $edge['bindings'],
                    'variants' => $variantSkus,
                ];

                $this->variantPayloads[] = $variantSkus;
            }

            $hasNextPage = $pageInfo['hasNextPage'];
            $cursor = $pageInfo['endCursor'];
        }

        $this->variantPayloads = array_flip(array_merge(...$this->variantPayloads));
    }

    public function hasVariantByCode(string $code): bool
    {
        return array_key_exists($code, $this->variantPayloads);
    }

    public function getVariantByCode(string $code): array
    {
        return $this->variantPayloads[$code];
    }

    public function storeVariant(array $variantNode): void
    {
        if (!array_key_exists('sku', $variantNode)) {
            throw SynchronizationException::invalidArrayStructure($variantNode, 'sku');
        }

        $this->variantPayloads[$variantNode['sku']] = $variantNode;
    }

    public function getVariantLists(): array
    {
        return $this->variantsLists;
    }
}
