<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Synchronizer;

use BitBag\SyliusErgonodePlugin\API\Client\ProductApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\ProductVariant\ProductVariantValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Collector\DeletedProductsCollectorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\ProductProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\ProductVariantProviderInterface;
use BitBag\SyliusErgonodePlugin\Storage\VariantCodesStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;

final class ProductSynchronizer implements SynchronizerInterface
{
    public function __construct(
        private readonly ProductApiClientInterface $productApiClient,
        private readonly ProductProviderInterface $productProvider,
        private readonly ProductVariantProviderInterface $productVariantProvider,
        private readonly ProductValuesAssignerInterface $productValuesAssigner,
        private readonly ProductVariantValuesAssignerInterface $productVariantValuesAssigner,
        private readonly EntityManagerInterface $entityManager,
        private readonly VariantCodesStorageInterface $variantCodesStorage,
        private readonly LoggerInterface $logger,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly DeletedProductsCollectorInterface $deletedProductsCollector,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $this->variantCodesStorage->initialize($integration);
        $this->deletedProductsCollector->collect($integration);
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

                continue;
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
            $this->synchronize($integration);

            return;
        }

        $variantLists = $this->variantCodesStorage->getVariantLists();
        $productCodes = array_keys($variantLists);
        $products = $this->productRepository->findBy(['code' => $productCodes]);
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $variants = $variantLists[$product->getCode()]['variants'];
            foreach ($variants as $variantCode) {
                if (!$this->variantCodesStorage->hasVariantByCode($variantCode)) {
                    continue;
                }

                try {
                    $variant = $this->productVariantProvider->provide($integration, $product, $variantCode);
                    $variantNode = $this->variantCodesStorage->getVariantByCode($variantCode);
                    $this->productVariantValuesAssigner->assign($variantNode, $integration, $variant);
                    $this->entityManager->persist($variant);
                } catch (\Throwable $e) {
                    $this->logger->error(sprintf('Error occurred during product variant synchronization: %s', $e->getMessage()), ['variantCode' => $variantCode]);
                }
            }
        }
        $this->entityManager->flush();

        $this->synchronizeDeletedProducts($integration);
    }

    private function getProductStream(IntegrationInterface $integration): ?array
    {
        $attributes = $this->productApiClient->getProducts($integration);

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

    private function synchronizeDeletedProducts(IntegrationInterface $integration): void
    {
        $codes = $this->deletedProductsCollector->getDeletedList();

        foreach ($this->productRepository->findBy(['code' => $codes]) as $product) {
            $this->entityManager->remove($product);
        }

        foreach ($this->productVariantRepository->findBy(['code' => $codes]) as $variant) {
            $this->entityManager->remove($variant);
        }

        $integration->setProductDeletedStreamCursorEnd($this->deletedProductsCollector->getCursorEnd());

        $this->entityManager->flush();
    }
}
