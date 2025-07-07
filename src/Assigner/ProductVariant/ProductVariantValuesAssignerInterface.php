<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\ProductVariant;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantValuesAssignerInterface
{
    public function assign(
        array $variantNode,
        IntegrationInterface $integration,
        ProductVariantInterface $productVariant,
    ): void;
}
