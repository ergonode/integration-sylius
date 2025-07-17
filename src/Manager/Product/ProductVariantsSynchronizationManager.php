<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Manager\Product;

use BitBag\SyliusErgonodePlugin\Assigner\ProductVariant\ProductVariantValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Provider\ProductOptionProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\ProductVariantProviderInterface;
use BitBag\SyliusErgonodePlugin\Storage\VariantCodesStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class ProductVariantsSynchronizationManager implements ProductVariantsSynchronizationManagerInterface
{
    public function __construct(
        private readonly ProductVariantValuesAssignerInterface $productVariantValuesAssigner,
        private readonly ProductOptionProviderInterface $productOptionProvider,
        private readonly ProductVariantProviderInterface $productVariantProvider,
        private readonly VariantCodesStorageInterface $variantCodesStorage,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function synchronizeVariants(IntegrationInterface $integration): void
    {
        $variantLists = $this->variantCodesStorage->getVariantLists();
        $productCodes = array_keys($variantLists);
        $products = $this->productRepository->findBy(['code' => $productCodes]);

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $variableProduct = $variantLists[$product->getCode()];
            $variants = $variableProduct['variants'];
            $productOptions = [];
            foreach ($variableProduct['options'] as $option) {
                $option = $this->productOptionProvider->provide($option, $integration);
                $productOptions[$option->getCode()] = $option;
                $this->entityManager->persist($option);
            }

            foreach ($variants as $variantCode) {
                if (!$this->variantCodesStorage->hasVariantByCode($variantCode)) {
                    continue;
                }

                try {
                    $variant = $this->productVariantProvider->provide($integration, $product, $variantCode);
                    $variantNode = $this->variantCodesStorage->getVariantByCode($variantCode);
                    $this->productVariantValuesAssigner->assign($variantNode, $integration, $variant, $productOptions);
                    $this->entityManager->persist($variant);
                } catch (\Throwable $e) {
                    $this->logger->error(sprintf('Error occurred during product variant synchronization: %s', $e->getMessage()), ['variantCode' => $variantCode]);
                }
            }
        }

        $this->entityManager->flush();
    }
}
