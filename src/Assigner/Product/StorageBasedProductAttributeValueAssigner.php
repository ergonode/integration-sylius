<?php

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
                case ProductAttributeValueInterface::STORAGE_JSON: {
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    $attributeValue->setValue($value);
                }break;
                case ProductAttributeValueInterface::STORAGE_BOOLEAN: {
                    $attributeValue->setValue((bool) $value);
                }break;
                case ProductAttributeValueInterface::STORAGE_INTEGER: {
                    $floatValue = $this->floatValueResolver->resolve($value);
                    $attributeValue->setValue((int) floor($floatValue));
                }break;
                case ProductAttributeValueInterface::STORAGE_FLOAT: {
                    $floatValue = $this->floatValueResolver->resolve($value);
                    $attributeValue->setValue($floatValue);
                }break;
                case ProductAttributeValueInterface::STORAGE_DATETIME:
                case ProductAttributeValueInterface::STORAGE_DATE: {
                    $value = new \DateTime($value);
                    $attributeValue->setValue($value);
                }break;
                case ProductAttributeValueInterface::STORAGE_TEXT: {
                    $locale = $attributeValue->getLocaleCode();
                    $value = $this->textValueResolver->resolve($locale, $value);

                    $attributeValue->setValue($value);
                }break;
            }
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error occured while assigning attribute value to product with id %s', $exception->getMessage()), ['value' => $value, 'attributeCode' => $attribute->getCode()]);
        }
    }
}
