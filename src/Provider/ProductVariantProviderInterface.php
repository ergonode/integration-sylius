<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantProviderInterface
{
    public function provide(
        IntegrationInterface $integration,
        ProductInterface $product,
        string $productVariantCode
    ): ProductVariantInterface;
}
