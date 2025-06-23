<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Enum;

enum ProductPropertyMapping: string
{
    case ENABLED = 'enabled';
    case SHIPPING_REQUIRED = 'shipping_required';
    case CHANNELS = 'channels';
    case PRODUCT_TYPE = 'product_type';
    case PRICE = 'price';
    case ORIGINAL_PRICE = 'original_price';
    case MINIMUM_PRICE = 'minimum_price';
    case TAX_CATEGORY = 'tax_category';
    case SHIPPING_CATEGORY = 'shipping_category';
    case WIDTH = 'width';
    case HEIGHT = 'height';
    case LENGTH = 'length';
    case CURRENT_STOCK = 'current_stock';
    case TRACKED = 'tracked';
    case NAME = 'name';
    case DESCRIPTION = 'description';
    case SLUG = 'slug';
    case META_KEYWORDS = 'meta_keywords';
    case META_DESCRIPTION = 'meta_description';
    case SHORT_DESCRIPTION = 'short_description';
    case MAIN_TAXON = 'main_taxon';
    case TAXONS = 'taxons';
    case MEDIA = 'media';
    case VARIANT_SELECTION_METHOD = 'variant_selection_method';
}
