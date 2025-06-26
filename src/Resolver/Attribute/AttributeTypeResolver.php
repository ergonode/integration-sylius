<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Resolver\Attribute;

use BitBag\SyliusErgonodePlugin\Enum\AttributeType;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use Sylius\Component\Attribute\AttributeType\DateAttributeType;
use Sylius\Component\Attribute\AttributeType\FloatAttributeType;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Attribute\AttributeType\TextareaAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;

final class AttributeTypeResolver implements AttributeTypeResolverInterface
{
    public function resolveType(AttributeType $type): string
    {
        switch ($type) {
            case AttributeType::TEXT:
            case AttributeType::UNIT:
                return TextAttributeType::TYPE;
            case AttributeType::NUMBER:
                return FloatAttributeType::TYPE;
            case AttributeType::TEXTAREA:
                return TextareaAttributeType::TYPE;
            case AttributeType::SELECT:
            case AttributeType::MULTISELECT:
                return SelectAttributeType::TYPE;
            case AttributeType::DATE:
                return DateAttributeType::TYPE;
            case AttributeType::GALLERY:
            case AttributeType::FILE:
            case AttributeType::IMAGE:
            case AttributeType::PRODUCT_RELATION:
            case AttributeType::PRICE:
                throw AttributeSynchronizationException::undefinedTargetType($type);
            default:
                throw AttributeSynchronizationException::undefinedTargetType($type);
        }
    }
}
