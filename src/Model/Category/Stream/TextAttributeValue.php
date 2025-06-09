<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Model\Category\Stream;

final class TextAttributeValue extends AttributeValue
{
    /** @param array<LocalizedText> $textTranslations*/
    public function __construct(
        private readonly array $textTranslations,
    ) {
    }

    /** @return array<LocalizedText> */
    public function getTextTranslations(): array
    {
        return $this->textTranslations;
    }
}
