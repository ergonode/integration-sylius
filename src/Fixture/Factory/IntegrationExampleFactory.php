<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Fixture\Factory;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationExampleFactory extends AbstractExampleFactory
{
    protected Generator $faker;

    protected OptionsResolver $optionsResolver;

    /**
     * @param FactoryInterface<IntegrationInterface> $integrationFactory
     * @param RepositoryInterface<IntegrationInterface> $integrationRepository
     * @param RepositoryInterface<LocaleInterface> $localeRepository
     */
    public function __construct(
        protected readonly FactoryInterface $integrationFactory,
        protected readonly RepositoryInterface $integrationRepository,
        protected readonly RepositoryInterface $localeRepository,
    ) {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('name', fn (Options $options): string => $this->faker->firstName)
            ->setDefault('code', fn (Options $options): string => $this->faker->numerify('ERGONODE_##'))
            ->setDefault('category_tree_code', fn (Options $options): string => $this->faker->countryCode())
            ->setDefault('main_language_code', function (Options $options): ?string {
                /** @var LocaleInterface[] $locales */
                $locales = $this->localeRepository->findAll();
                shuffle($locales);

                return array_pop($locales)?->getCode();
            })
            ->setDefault('api_url', fn (Options $options): string => $this->faker->url())
            ->setDefault('api_key', fn (Options $options): string => $this->faker->sha256())
            ->setDefault('enabled', fn (Options $options): bool => $this->faker->boolean(70))
            ->setAllowedTypes('name', ['null', 'string'])
            ->setAllowedTypes('code', ['string'])
            ->setAllowedTypes('enabled', ['null', 'bool'])
            ->setAllowedTypes('category_tree_code', ['null', 'string'])
            ->setAllowedTypes('main_language_code', ['null', 'string'])
            ->setAllowedTypes('api_url', ['null', 'string'])
            ->setAllowedTypes('api_key', ['null', 'string'])
        ;
    }

    public function create(array $options = []): IntegrationInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var IntegrationInterface $integration */
        $integration = $this->integrationFactory->createNew();
        $integration->setName($options['name']);
        $integration->setCode($options['code']);
        $integration->setCategoryTreeCode($options['category_tree_code']);
        $integration->setMainLanguageCode($options['main_language_code']);
        $integration->setApiUrl($options['api_url']);
        $integration->setApiKey($options['api_key']);
        $integration->setEnabled($options['enabled']);

        return $integration;
    }
}
