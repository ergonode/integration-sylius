<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Model\ResourceInterface;

interface VariantResourceInterface extends ResourceInterface, ProductVariantInterface
{

}
