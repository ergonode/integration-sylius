# Adding a Property to a Category or Attribute

To add a custom property to an existing **category** or **attribute**, you can extend the plugin’s functionality through composition or inheritance, by decorating the relevant vendor service.

For **attributes**, use the following base service:
`BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributePropertyValueAssigner`.

Example implementation:

```php
<?php

declare(strict_types=1);

namespace Foo\Bar\Assigner;
...
use BitBag\SyliusErgonodePlugin\Assigner\Attribute\AttributePropertyValueAssigner as BaseAttributePropertyValueAssigner 
...

final readonly class AttributePropertyValueAssigner extends BaseAttributePropertyValueAssigner implements AttributePropertyValueAssignerInterface
{
    public function assign(
        AttributeInterface $attribute,
        AttributePropertyMapping $propertyMapping,
        mixed $attributeValue,
    ): void {
        // Check which of your properties should be resolved
        
        parent::assign($attribute, $propertyMapping, $attributeValue);
    }
```

# Adding property to product or variant
Adding a Property to a Product or Variant

Because of the number of properties involved and the overlap between products and variants, the structure here is slightly more complex.

Let’s assume you want to assign a supplier to both products and variants.

To do this:
1.	Create a class that extends `BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner`, or one of its abstract subclasses.
2.	Implement the supports method to define whether the assigner applies to a given resource.
3.	Implement the assign method to set the property based on the Ergonode input.

```php
<?php

declare(strict_types=1);

namespace Foo\Bar\Assigner\Property\Product;

use BitBag\SyliusErgonodePlugin\Assigner\Property\PropertyValueAssigner;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ProductVariantResolverInterface;
use Psr\Log\LoggerInterface;
use Foo\Bar\Entity\SupplierInterface;
use Foo\Bar\Enum\ProductVariantPropertyMapping;
use Foo\Bar\Enum\ProductPropertyMapping;
use Foo\Bar\Provider\SupplierProviderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class SupplierValueAssigner extends PropertyValueAssigner
{
    public function __construct(
        private readonly SupplierProviderInterface $supplierProvider,
        private readonly ProductVariantResolverInterface $productVariantResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function assign(ResourceInterface $resource, array $values, ?ResourceRelationMappingInterface $mapping): void
    {
        $value = array_shift($values);
        $supplier = $this->supplierProvider->getSupplierByIdentifier($value[0]);

        if ($supplier instanceof SupplierInterface || null === $value) {
            $variant = $this->productVariantResolver->getVariant($resource);
            $variant->setSupplier($supplier);

            return;
        }

        $this->logger->warning(sprintf('Supplier with id "%s" not found.', $value));
    }

    public function supports(ResourceInterface $resource, string $property): bool
    {
        $isVariant = $resource instanceof ProductVariantInterface;
        $productWithSingleVariant = $resource instanceof ProductInterface && 1 === $resource->getVariants()->count();

        if (!$isVariant && !$productWithSingleVariant) {
            return false;
        }

        return ProductPropertyMapping::SHIPPING_CATEGORY === ProductPropertyMapping::tryFrom($property) ||
            ProductVariantPropertyMapping::SHIPPING_CATEGORY === ProductVariantPropertyMapping::tryFrom($property);
    }
}
```

Finally, register the assigner service and tag it appropriately in your `services.xml`:
```xml
    ...
        <service id="foo.bar.assigner.property.product.supplier_assigner" class="Foo\Bar\Assigner\Property\Product\SupplierAssigner">
            <argument type="service" id="foo.bar.provider.supplier_provider"/>
            <argument type="service" id="bitbag.sylius_ergonode_plugin.logger.prefixed_logger"/>
            <tag name="bitbag.sylius_ergonode_plugin.product_property_assigner"/>
            <tag name="bitbag.sylius_ergonode_plugin.product_variant_property_assigner"/>
        </service>
    ...
```
This ensures your assigner is discovered and executed by the plugin during synchronization.
