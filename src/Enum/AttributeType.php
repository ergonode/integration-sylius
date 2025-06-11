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
    case TEXT = 'TextAttributeValue';
    case TEXTAREA = 'TextareaAttributeValue';
    case DATE = 'DateAttributeValue';
    case NUMBER = 'NumberAttributeValue';
    case SELECT = 'SelectAttributeValue';
    case MULTISELECT = 'MultiSelectAttributeValue';
    case UNIT = 'UnitAttributeValue';
    case IMAGE = 'ImageAttributeValue';
    case GALLERY = 'GalleryAttributeValue';
    case FILE = 'FileAttributeValue';
    case PRODUCT_RELATION = 'ProductRelationAttributeValue';
}
