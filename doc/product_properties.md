# Ergonode Product Attribute to Sylius Product Property

This section outlines how to map Ergonode attributes to specific properties of Sylius products and product variants during synchronization.

Ergonode allows products to be built using flexible, typed attributes (e.g., `text`, `select`). To synchronize them meaningfully, it's important to determine which attribute values should populate which structured properties within Sylius.

These mappings are configured using the **Resource Relation Mapping** interface and are crucial to ensuring that key product data — such as name, slug, description, SKU, and price — is stored correctly.

---

## Why This Mapping Matters

- Some Ergonode attributes are **semantic** (e.g., "Title", "Price", "SKU") and should map to **dedicated fields** in Sylius.
- Others can be treated as **custom attributes** and imported as `ProductAttributeValue` entries.
- Correct mappings ensure proper:
    - Variant and product identification
    - Localization and translations
    - Pricing across channels
    - Tax and shipping configuration

---

## Example Use Cases

| Sylius Product Property | Suggested Ergonode Type | Translatable | Notes                                                                              |
|-------------------------|--------------------------|--------------|------------------------------------------------------------------------------------|
| `Name`                  | `Text`                   | ✅            |                                                                                    |
| `Description`           | `Text` / `Textarea`      | ✅            |                                                                                    |
| `Slug`                  | `Text`                   | ✅            | Used in product URLs. Avoid spaces or special characters.                          |
| `Meta keywords`         | `Text`                   | ✅            |                                                                                    |
| `Meta description`      | `Text`                   | ✅            |                                                                                    |
| `Short description`     | `Text` / `Textarea`      | ✅            |                                                                                    |
| `Enabled`               | `Select`                 | ❌            | [Attribute must be defined as boolean](attribute_properties.md#boolean-attributes) |
| `Tracked`               | `Select`                 | ❌            | [Attribute must be defined as boolean](attribute_properties.md#boolean-attributes)                                                   |
| `Shipping Required`     | `Select`                 | ❌            | [Attribute must be defined as boolean](attribute_properties.md#boolean-attributes)                                                   |
| `Channels`              | `Multi Select`           | ❌            | Option codes should match existing Sylius channel codes.                           |
| `Tax Category`          | `Select`                 | ❌            | Option codes should match existing Sylius tax category codes.                      |
| `Shipping Category`     | `Select`                 | ❌            | Option codes should match existing Sylius shipping category codes.                 |
| `Main taxon`            | `Select`                 | ❌            | Option codes should match Sylius taxon codes.                                      |
| `Price`                 | `Numeric` / `Price`      | ❌            | Channel-based; mapping should specify the Sylius channel code.                     |
| `Original Price`        | `Numeric` / `Price`      | ❌            | Channel-based; mapping should specify the Sylius channel code.                     |
| `Minimum Price`         | `Numeric` / `Price`      | ❌            | Channel-based; mapping should specify the Sylius channel code.                     |
| `Width`                 | `Numeric`                | ❌            | Must be greater than 0.                                                            |
| `Height`                | `Numeric`                | ❌            | Must be greater than 0.                                                            |
| `Depth`                 | `Numeric`                | ❌            | Must be greater than 0.                                                            |
| `Weight`                | `Numeric`                | ❌            | Must be greater than 0.                                                            |
| `Current stock`         | `Numeric`                | ❌            | Must be greater than 0.                                                            |
| `Media`                 | `Gallery`                | ❌            |                                                                                    |

---

## Notes
- All Ergonode product attribute values that won't be assigned as Sylius product properties will be treated as product attribute values and synchronized as such. 
- Boolean-like attributes (e.g., `Enabled`, `Tracked`) must use `Select` attributes in Ergonode, with values `0` or `1` with `prefer_code_over_name` flag. Refer to [boolean attributes](attribute_properties.md#boolean-attributes)
- Channel-specific prices must be defined with channel context to avoid misrouting in multi-channel Sylius setups.
- Translatable fields should use Ergonode attributes with translations enabled for proper localization.
