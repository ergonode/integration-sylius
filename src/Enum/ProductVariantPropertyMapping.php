<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Enum;

enum ProductVariantPropertyMapping: string
{
    case ENABLED = 'enabled';
    case SHIPPING_REQUIRED = 'shipping_required';
    case PRICE = 'price';
    case ORIGINAL_PRICE = 'original_price';
    case MINIMUM_PRICE = 'minimum_price';
    case TAX_CATEGORY = 'tax_category';
    case SHIPPING_CATEGORY = 'shipping_category';
    case WIDTH = 'width';
    case HEIGHT = 'height';
    case DEPTH = 'depth';
    case CURRENT_STOCK = 'current_stock';
    case TRACKED = 'tracked';
    case NAME = 'name';
    case MAIN_TAXON = 'main_taxon';
    case TAXONS = 'taxons';
    case MEDIA = 'media';
    case VARIANT_SELECTION_METHOD = 'variant_selection_method';
}
