<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Context;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Repository\ResourceRelationMappingRepositoryInterface;

final class MainLanguageContext implements MainLanguageContextInterface
{
    private array $mainLanguage = [];

    public function __construct(
        private readonly ResourceRelationMappingRepositoryInterface $resourceRelationMappingRepository,
    ) {
    }

    public function getMainLanguageMapping(IntegrationInterface $integration): ResourceRelationMappingInterface
    {
        $identifier = sprintf('integration-%s', $integration->getId());

        if (!array_key_exists($identifier, $this->mainLanguage)) {
            if (null === $integration->getMainLanguageCode()) {
                throw SynchronizationException::missingCategoryTreeCode($integration);
            }

            $this->mainLanguage[$identifier] = $this->resourceRelationMappingRepository->findOneByIntegrationAndTypeAndSyliusValue(
                $integration,
                RelationType::LANGUAGE_EXTERNAL_ID,
                $integration->getMainLanguageCode(),
            );
        }

        if (null === $this->mainLanguage[$identifier]) {
            throw SynchronizationException::noMainLanguageMappingFound($integration);
        }

        return $this->mainLanguage[$identifier];
    }
}
