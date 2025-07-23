<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use BitBag\SyliusErgonodePlugin\Resolver\FloatValueResolverInterface;
use BitBag\SyliusErgonodePlugin\Resolver\Value\TextValueResolverInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Webmozart\Assert\Assert;

readonly class StorageBasedProductAttributeValueAssigner implements StorageBasedProductAttributeValueAssignerInterface
{
    public function __construct(
        private FloatValueResolverInterface $floatValueResolver,
        private TextValueResolverInterface $textValueResolver,
        private LoggerInterface $logger,
    ) {
    }

    public function assign(ProductAttributeValueInterface $attributeValue, mixed $value): void
    {
        try {
            $attribute = $attributeValue->getAttribute();
            Assert::isInstanceOf($attribute, AttributeInterface::class);

            switch ($attribute->getStorageType()) {
                case ProductAttributeValueInterface::STORAGE_JSON:
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    $attributeValue->setValue($value);

                    break;
                case ProductAttributeValueInterface::STORAGE_BOOLEAN:
                    $attributeValue->setValue((bool) $value);

                    break;
                case ProductAttributeValueInterface::STORAGE_INTEGER:
                    $floatValue = $this->floatValueResolver->resolve($value);
                    if (null !== $floatValue) {
                        $attributeValue->setValue((int) floor($floatValue));
                    }

                    break;
                case ProductAttributeValueInterface::STORAGE_FLOAT:
                    $floatValue = $this->floatValueResolver->resolve($value);
                    if (null === $floatValue) {
                        return;
                    }

                    $attributeValue->setValue($floatValue);

                    break;
                case ProductAttributeValueInterface::STORAGE_DATETIME:
                case ProductAttributeValueInterface::STORAGE_DATE:
                    $value = new \DateTime($value);
                    $attributeValue->setValue($value);

                    break;
                case ProductAttributeValueInterface::STORAGE_TEXT:
                    $locale = $attributeValue->getLocaleCode();
                    Assert::notNull($locale);

                    $value = $this->textValueResolver->resolve($locale, $value);

                    $attributeValue->setValue($value);

                    break;
            }
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error occured while assigning attribute value to product with id %s', $exception->getMessage()), ['value' => $value, 'attributeCode' => $attribute?->getCode()]);
        }
    }
}
