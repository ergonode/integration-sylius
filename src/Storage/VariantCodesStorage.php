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
    private array $variants = [];

    private array $variantLists = [];

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
                if (!array_key_exists('sku', $edge)) {
                    continue;
                }

                $this->variants[] = $edge['sku'];
            }

            $hasNextPage = $pageInfo['hasNextPage'];
            $cursor = $pageInfo['endCursor'];
        }

        $this->variants = array_flip(array_unique($this->variants));
    }

    public function hasVariantByCode(string $code): bool
    {
        return array_key_exists($code, $this->variants);
    }

    public function getVariantByCode(string $code): array
    {
        return $this->variants[$code];
    }

    public function storeVariant(array $variantNode): void
    {
        if (!array_key_exists('code', $variantNode)) {
            throw SynchronizationException::invalidArrayStructure($variantNode, 'code');
        }

        $this->variants[$variantNode['code']] = $variantNode;
    }

    public function storeVariantList(string $productCode, array $variantsList): void
    {
        $this->variantLists[$productCode] = $variantsList;
    }

    public function getVariantLists(): array
    {
        return $this->variantLists;
    }

    public function unsetVariant(string $code): void
    {
        unset($this->variants[$code], $this->variantLists[$code]);
    }
}
