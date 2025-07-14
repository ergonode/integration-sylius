<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Attribute;

use BitBag\SyliusErgonodePlugin\Assigner\Translation\TranslationAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;

final readonly class AttributeNameAssigner implements AttributeNameAssignerInterface
{
    public function __construct(
        private ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private TranslationAssignerInterface $attributeTranslationAssigner,
    ) {
    }

    public function assign(
        IntegrationInterface $integration,
        AttributeInterface $attribute,
        array $attributeNames,
    ): void {
        foreach ($attributeNames as $name) {
            $language = $name['language'];
            $languageMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue(
                $integration,
                $language,
                RelationType::LANGUAGE_EXTERNAL_ID,
            );
            $localeCode = $languageMapping?->getSyliusValue();

            if (null === $localeCode) {
                continue;
            }

            if (null === $name['value']) {
                continue;
            }

            $translation = $attribute->getTranslation($localeCode);
            if ($localeCode !== $translation->getLocale()) {
                $this->attributeTranslationAssigner->assign($attribute, $localeCode);
                $translation = $attribute->getTranslation($localeCode);
            }

            $translation->setName($name['value']);
        }
    }
}
