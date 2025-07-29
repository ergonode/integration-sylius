# Ergonode Attribute Metadatum to Sylius Product Attribute Property

This section describes how to map Ergonode attribute metadata to structural properties of **Sylius product attributes** during synchronization.

While Ergonode focuses on flexible, content-driven attribute definitions, Sylius requires more structure around product attributes (e.g., whether an attribute is translatable, what input type it uses, how many entries are allowed, etc.).

Using **Resource Relation Mapping**, you can define how each metadata value from Ergonode should influence a Sylius `ProductAttribute`.

---

## Why This Mapping Matters

- Helps define how attributes behave in Sylius (e.g., is it multi-select, is it localized?).
- Controls frontend form rendering and validation (e.g., min/max length).
- Reduces the need for post-import manual attribute configuration in the Sylius admin panel.

---

## Example Use Cases

| Sylius Attribute Property | Ergonode metadatum key  | Notes                                                                                                                                                  |
|--------------------------|-------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Type`                   | `type`                  | Maps to the Sylius `type` (text, select, integer, etc.).                                                                                               |
| `Translatable`           | `translatable`          | Values must be `0` or `1`. Affects whether the attribute supports translations.                                                                        |
| `Position`               | `position`              | Controls ordering of attributes in the admin and storefront.                                                                                           |
| `Min length`             | `min_length`            | Only applicable for textual fields. Must be >= 0.                                                                                                      |
| `Max length`             | `max_length`            | Only applicable for textual fields. Must be >= `min_length`.                                                                                           |
| `Min entries`            | `min_entries`           | Used for multi-select fields to restrict minimum number of selections.                                                                                 |
| `Max entries`            | `max_entries`           | Used for multi-select fields to restrict maximum number of selections.                                                                                 |
| `Multiple`               | `multiple`              | Values must be `0` or `1`. Indicates if multiple values are allowed.                                                                                   |
| `<unmapped>`              | `prefer_code_over_name` | This value is not mapped or retained in Sylius, it defines whether the name or code of certain select value should be used. Values must be `0` or `1`. |

---
## Boolean attributes
Boolean-like attributes (e.g., `Enabled`, `Tracked`) must use `Select` attributes in Ergonode, with values `0` or `1` and `prefer_code_over_name` metadatum set to `1`.
It is strongly advised for the sake of smooth synchronization and seamless integration between Ergonode and Sylius.   

---

## Notes
- Sylius Product Attribute property `name` will be imported based on corresponding Ergonode Attribute property.
- The values of `0` and `1` must be sent as **option codes** in Ergonode, not as strings like `"yes"` or `"true"`.
- Most of these mappings only apply during **initial attribute creation**; changing them later may require manual cleanup in Sylius (e.g., migrating attribute values).
- Metadata with keys absent on the list will be skipped during synchronization.  
