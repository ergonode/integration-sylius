<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Translation;

use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Resource\Model\TranslatableInterface;
use Webmozart\Assert\Assert;

final class ProductOptionTranslationAssigner implements TranslationAssignerInterface
{
    public function __construct(
        private readonly FactoryInterface $productOptionTranslationFactory,
    ) {
    }

    public function assign(TranslatableInterface $translatable, string $syliusLocaleCode): void
    {
        Assert::isInstanceOf($translatable, AttributeInterface::class);
        /** @var TranslationInterface $productOptionTranslation */
        $productOptionTranslation = $this->productOptionTranslationFactory->createNew();
        $productOptionTranslation->setLocale($syliusLocaleCode);
        $translatable->addTranslation($productOptionTranslation);
    }
}
