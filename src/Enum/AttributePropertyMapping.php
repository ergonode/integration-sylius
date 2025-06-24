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
    case TYPE = 'type';
    case TRANSLATABLE = 'translatable';
    case POSITION = 'position';
    case MIN_LENGTH = 'min_length';
    case MAX_LENGTH = 'max_length';
    case MIN_ENTRIES = 'min_entries';
    case MAX_ENTRIES = 'max_entries';
    case MULTIPLE = 'multiple';
}
