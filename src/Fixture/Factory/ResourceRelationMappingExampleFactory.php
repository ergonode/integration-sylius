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
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceRelationMappingExampleFactory extends AbstractExampleFactory
{
    protected Generator $faker;

    protected OptionsResolver $optionsResolver;

    /**
     * @param FactoryInterface<ResourceRelationMappingInterface> $resourceRelationMappingFactory
     * @param RepositoryInterface<IntegrationInterface> $integrationRepository
     */
    public function __construct(
        protected readonly FactoryInterface $resourceRelationMappingFactory,
        protected readonly RepositoryInterface $integrationRepository,
    ) {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['integration', 'type', 'sylius_value', 'ergonode_value', 'import_once', 'channel_code'])
            ->setAllowedTypes('integration', ['string', IntegrationInterface::class])
            ->setNormalizer('integration', LazyOption::getOneBy($this->integrationRepository, 'code'))
            ->setAllowedTypes('type', ['string'])
            ->setAllowedTypes('sylius_value', ['string'])
            ->setAllowedTypes('ergonode_value', ['string'])
            ->setAllowedTypes('import_once', ['bool'])
            ->setAllowedTypes('channel_code', ['null', 'string'])
            ->setDefault('import_once', false)
            ->setDefault('channel_code', 'null')
        ;
    }

    public function create(array $options = []): ResourceRelationMappingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var ResourceRelationMappingInterface $resourceRelationMapping */
        $resourceRelationMapping = $this->resourceRelationMappingFactory->createNew();
        $resourceRelationMapping->setRelationType(RelationType::tryFrom($options['type']));
        $resourceRelationMapping->setIntegration($options['integration']);
        $resourceRelationMapping->setImportOnce($options['import_once']);
        $resourceRelationMapping->setSyliusValue($options['sylius_value']);
        $resourceRelationMapping->setErgonodeValue($options['ergonode_value']);
        $resourceRelationMapping->setChannelCode($options['channel_code']);

        return $resourceRelationMapping;
    }
}
