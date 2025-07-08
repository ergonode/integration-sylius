<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Updater;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;

interface ProductAssociationTypeUpdaterInterface
{
    public function update(
        IntegrationInterface $integration,
        array $associationTypeNode
    ): ProductAssociationTypeInterface;
}
