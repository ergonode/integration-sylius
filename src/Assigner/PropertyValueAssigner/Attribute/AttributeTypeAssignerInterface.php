<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute;

use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use Sylius\Component\Attribute\Model\AttributeInterface;

interface AttributeTypeAssignerInterface
{
    public function assignByEnum(
        AttributeInterface $attribute,
        AttributeType $type,
    ): void;

    public function assignByMetadatumValue(
        AttributeInterface $attribute,
        string $type,
    ): void;
}
