<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Storage;

use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Repository\ProductAssociationTypeRepositoryInterface;

final class ProductAssociationTypeStorage implements ProductAssociationTypeStorageInterface
{
    private array $associations = [];

    public function __construct(
        private readonly ProductAssociationTypeRepositoryInterface $productAssociationTypeRepository,
    ) {
    }

    public function getAssociationByCode(string $code): ?ProductAssociationTypeInterface
    {
        if ([] === $this->associations) {
            $this->preload();
        }

        if (!array_key_exists($code, $this->associations)) {
            return null;
        }

        return $this->associations[$code];
    }

    private function preload(): void
    {
        /** @var array<ProductAssociationTypeInterface> $associations */
        $associations = $this->productAssociationTypeRepository->findAll();

        $this->associations = array_merge(
            ...array_map(function (ProductAssociationTypeInterface $productAssociationType) {
                return [$productAssociationType->getCode() => $productAssociationType];
            }, $associations),
        );
    }
}
