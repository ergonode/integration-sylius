<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Enum;

enum AttributePropertyMapping: string
{
    case CODE = 'code';
    case PRODUCT_TYPE = 'type';
    case ENABLED = 'translatable';
    case POSITION = 'position';
    case NAME = 'name';
    case MIN_LENGTH = 'min_length';
    case MAX_LENGTH = 'max_length';
    case DATETIME_FORMAT = 'datetime_format';
    case DATE_FORMAT = 'date_format';
    case MIN_ENTRIES = 'min_entries';
    case MAX_ENTRIES = 'max_entries';
    case MULTIPLE = 'multiple';
}
