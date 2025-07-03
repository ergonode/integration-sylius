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
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\ProductProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\ProductVariantProviderInterface;
use BitBag\SyliusErgonodePlugin\Storage\VariantCodesStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class ProductSynchronizer implements SynchronizerInterface
{
    public function __construct(
        private readonly ProductApiClientInterface $productApiClient,
        private readonly ProductProviderInterface $productProvider,
        private readonly ProductVariantProviderInterface $productVariantProvider,
        private readonly ProductValuesAssignerInterface $productValuesAssigner,
        private readonly EntityManagerInterface $entityManager,
        private readonly VariantCodesStorageInterface $variantCodesStorage,
        private readonly LoggerInterface $logger,
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $this->variantCodesStorage->initialize($integration);
        $productStream = $this->getProductStream($integration);
        if (null === $productStream) {
            return;
        }
        foreach ($productStream['edges'] as $productEdge) {
            $productNode = $productEdge['node'];
            $code = $productNode['sku'];
            if ($this->variantCodesStorage->hasVariantByCode($code)) {
                $this->variantCodesStorage->storeVariant($productNode);
            }

            $product = $this->productProvider->provide($integration, $code);

            try {
                $this->productValuesAssigner->assign($productNode, $integration, $product);
                if ('VariableProduct' === $productNode['typename']) {
                    $this->variantCodesStorage->storeVariantList($code, $productNode['variantList']);
                }
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

        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        $this->logger->info(sprintf('Previously mentioned product have been saved.'));

        if (false !== $productStream['pageInfo']['hasNextPage'] &&
            null !== $productStream['pageInfo']['endCursor']
        ) {
            $integration->setProductStreamCursorEnd($productStream['pageInfo']['endCursor']);
            $this->synchronize($integration);

            return;
        }

        $variantLists = $this->variantCodesStorage->getVariantLists();
        $products = $this->productRepository->findBy(['code' => array_keys($variantLists)]);
        foreach ($products as $product) {
            $variants = $variantLists[$product->getCode()];
            foreach ($variants as $variantCode) {
                if (!$this->variantCodesStorage->hasVariantByCode($variantCode)) {
                    continue;
                }

                $variant = $this->productVariantProvider->provide($integration, $product, $variantCode);
                $variantNode = $this->variantCodesStorage->getVariantByCode($variantCode);
                $this->productVariantValuesAssigner->assign($variant, $integration, $variantNode);
            }
            $this->entityManager->persist($variant);
        }
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
}
