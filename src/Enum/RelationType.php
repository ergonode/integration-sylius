<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Enum;

enum RelationType: string
{
    case ATTRIBUTE_CODE = 'attribute_property';
    case CATEGORY_CODE = 'category_property';
    case VARIANT_CODE = 'variant_property';
    case PRODUCT_CODE = 'product_property';
    case ATTRIBUTE_EXTERNAL_ID = 'attribute_external_identifier';
    case CATEGORY_EXTERNAL_ID = 'category_external_identifier';
    case VARIANT_EXTERNAL_ID = 'variant_external_identifier';
    case PRODUCT_EXTERNAL_ID = 'product_external_identifier';
    case LANGUAGE_EXTERNAL_ID = 'locale_external_identifier';
}
