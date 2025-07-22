<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Manager\Product;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductTaxonsAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\ProductProviderInterface;
use BitBag\SyliusErgonodePlugin\Storage\VariantCodesStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class ProductSynchronizationManager implements ProductSynchronizationManagerInterface
{
    public function __construct(
        private readonly ProductApiClientInterface $productApiClient,
        private readonly ProductProviderInterface $productProvider,
        private readonly VariantCodesStorageInterface $variantCodesStorage,
        private readonly ProductValuesAssignerInterface $productValuesAssigner,
        private readonly ProductTaxonsAssignerInterface $productTaxonsAssigner,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function manage(IntegrationInterface $integration): void
    {
        $productStream = $this->getProductStream($integration);
        if (null === $productStream) {
            return;
        }

        foreach ($productStream['edges'] as $productEdge) {
            $productNode = $productEdge['node'];
            $code = $productNode['sku'];
            $typename = $productNode['typename'];
            if ($this->variantCodesStorage->hasVariantByCode($code)) {
                $this->variantCodesStorage->storeVariant($productNode);

                continue;
            }
            $isVariableProduct = 'VariableProduct' === $typename;
            $product = $this->productProvider->provide($integration, $code, $isVariableProduct);

            try {
                $this->productValuesAssigner->assign($productNode, $integration, $product);
                $this->productTaxonsAssigner->assign($productNode, $integration, $product);
            } catch (SynchronizationException $exception) {
                $this->logger->error($exception->getMessage(), [$exception]);
                $this->logger->info(sprintf('Product `%s` could not have been synchronized.', $product->getCode()));

                continue;
            } catch (\Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Unexpected exception occurred during product synchronization, message: %s',
                        $exception->getMessage(),
                    ),
                    [$exception],
                );

                return;
            }
            $this->logger->info(sprintf('Product `%s` has been synchronized and will be persisted.', $product->getCode()));
            $this->entityManager->persist($product);
        }

        if (null !== $productStream['pageInfo']['endCursor']) {
            $integration->setProductStreamCursorEnd($productStream['pageInfo']['endCursor']);
        }

        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        $this->logger->info(sprintf('Previously mentioned product have been saved.'));

        if (false !== $productStream['pageInfo']['hasNextPage']
        ) {
            $this->manage($integration);
        }
    }

    private function getProductStream(IntegrationInterface $integration): ?array
    {
        $attributes = $this->productApiClient->getProducts($integration, $integration->getProductStreamCursorEnd());

        if (
            !array_key_exists('data', $attributes) ||
            !is_array($attributes['data']) ||
            !array_key_exists('productStream', $attributes['data']) ||
            !is_array($attributes['data']['productStream'])
        ) {
            return null;
        }

        return $attributes['data']['productStream'];
    }
}
