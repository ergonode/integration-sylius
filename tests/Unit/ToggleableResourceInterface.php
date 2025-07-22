<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Model\ResourceInterface;

interface ToggleableResourceInterface extends ResourceInterface, ProductInterface
{
}
