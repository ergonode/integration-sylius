<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Enum\AttributePropertyMapping;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use Sylius\Component\Attribute\Model\AttributeInterface;

readonly class AttributePropertyValueAssigner implements AttributePropertyValueAssignerInterface
{
    public function __construct(
        private AttributeTypeAssignerInterface $attributeTypeAssigner,
    ) {
    }

    public function assign(
        AttributeInterface $attribute,
        AttributePropertyMapping $propertyMapping,
        mixed $attributeValue,
    ): void {
        switch ($propertyMapping) {
            case AttributePropertyMapping::POSITION:
                $this->assignPosition($attribute, $attributeValue);

                return;
            case AttributePropertyMapping::TRANSLATABLE:
                $attribute->setTranslatable((bool) $attributeValue);

                return;
            case AttributePropertyMapping::MULTIPLE:
                $configuration = $attribute->getConfiguration();
                $configuration['multiple'] = (bool) $attributeValue;

                $attribute->setConfiguration($configuration);

                return;
            case AttributePropertyMapping::MAX_ENTRIES:
            case AttributePropertyMapping::MAX_LENGTH:
                $configuration = $attribute->getConfiguration();
                $configuration['max'] = $this->getInt($attributeValue);

                $attribute->setConfiguration($configuration);

                return;
            case AttributePropertyMapping::MIN_ENTRIES:
            case AttributePropertyMapping::MIN_LENGTH:
                $configuration = $attribute->getConfiguration();
                $configuration['min'] = $this->getInt($attributeValue);

                $attribute->setConfiguration($configuration);

                return;
            case AttributePropertyMapping::TYPE:
                $this->attributeTypeAssigner->assignByMetadatumValue($attribute, $attributeValue);

                return;
        }
    }

    private function assignPosition(AttributeInterface $attribute, mixed $attributeValue): void
    {
        $attribute->setPosition($this->getInt($attributeValue));
    }

    private function getInt(mixed $value): int
    {
        if (!(is_string($value) && ctype_digit($value))) {
            throw new AttributeSynchronizationException(
                sprintf('Position value must be an integer or be possible to transform to integer. Given value: `%s`.', $value),
            );
        }

        return (int) $value;
    }
}
