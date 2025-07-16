# Ergonode Category Attribute to Sylius Taxon Property

This section explains how to map Ergonode category attributes to structured properties of Sylius taxons during synchronization.

In Ergonode, categories are flexible and can include additional attributes. To ensure these attributes are properly reflected in Sylius, you can configure **Resource Relation Mappings** to assign specific values to taxon properties.

---

## Why This Mapping Matters

- Sylius taxons are used for storefront navigation and product classification.
- Ergonode categories may include unmapped attributes, which could not be assigned to certain properties.

---

## Example Use Cases


| Sylius Taxon Property | Suggested Ergonode Type | Translatable | Notes                                                                                             |
|-----------------------|--------------------------|--------------|---------------------------------------------------------------------------------------------------|
| `Description`         | `Text` / `Textarea`      | ✅            |                                                                                                   |
| `Slug`                | `Text`                   | ✅            | Used in product URLs. Avoid spaces or special characters.                                         |
| `Enabled`             | `Select`                 | ❌            | Option codes must be `0` or `1`.                                                                  |
| `Media`                 | `Gallery`                | ❌            |                                  |
---

## Notes

- Translatable Ergonode attributes should be localized per locale, especially in multi-language stores.
- The `Slug` attribute must be URL-safe (lowercase, hyphens, no spaces).
- Sylius Taxon property `name` will be imported based on corresponding Ergonode Category property.
- Additional properties will be omitted during synchronization. 


