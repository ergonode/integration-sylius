<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorage;
use Tests\BitBag\SyliusErgonodePlugin\Behat\Factory\IntegrationFactoryInterface;

final class IntegrationContext implements Context
{
    public function __construct(
        private readonly IntegrationFactoryInterface $integrationFactory,
        private readonly SharedStorage $sharedStorage,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Given('there is an integration named :name')]
    public function thereIsAnIntegrationInTheStore(string $name): void
    {
        $integration = $this->integrationFactory->createNamed($name);
        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        $this->sharedStorage->set('integration', $integration);
    }

    #[Given('there is an integration')]
    public function thereIsAnIntegration(): void
    {
        $integration = $this->integrationFactory->createNew();

        $this->sharedStorage->set('integration', $integration);
    }

    #[Given('integration has category tree code :categoryTree specified')]
    public function integrationHasCategoryTreeSpecified(string $categoryTree): void
    {
        $this->sharedStorage->get('integration')->setCategoryTreeCode($categoryTree);
    }
}
