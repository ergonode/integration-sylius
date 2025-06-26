<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Enum;

enum AttributeType: string
{
    case TEXT = 'TextAttribute';
    case TEXTAREA = 'TextareaAttribute';
    case DATE = 'DateAttribute';
    case NUMBER = 'NumericAttribute';
    case SELECT = 'SelectAttribute';
    case MULTISELECT = 'MultiSelectAttribute';
    case UNIT = 'UnitAttribute';
    case PRICE = 'PriceAttribute';
    case IMAGE = 'ImageAttribute';
    case GALLERY = 'GalleryAttribute';
    case FILE = 'FileAttribute';
    case PRODUCT_RELATION = 'ProductRelationAttribute';
}
