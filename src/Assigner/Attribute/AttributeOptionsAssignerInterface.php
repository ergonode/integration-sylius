<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;

interface AttributeOptionsAssignerInterface
{
    public function assign(IntegrationInterface $integration, AttributeInterface $attribute, string $ergonodeAttributeCode, array $optionList): void;
}
