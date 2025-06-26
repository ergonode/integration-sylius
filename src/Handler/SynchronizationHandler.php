<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Handler;

use BitBag\SyliusErgonodePlugin\Context\FullSynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Exception\IntegrationException;
use BitBag\SyliusErgonodePlugin\Message\Synchronization;
use BitBag\SyliusErgonodePlugin\Repository\IntegrationRepositoryInterface;
use BitBag\SyliusErgonodePlugin\Synchronizer\SynchronizerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SynchronizationHandler
{
    /**@param iterable<SynchronizerInterface> $synchronizers*/
    public function __construct(
        private iterable $synchronizers,
        private IntegrationRepositoryInterface $integrationRepository,
        private FullSynchronizationContextInterface $fullSynchronizationContext,
    ) {
    }

    public function __invoke(Synchronization $synchronization): void
    {
        $integration = $this->integrationRepository->findOneEnabledById($synchronization->getIntegrationId());
        if (null === $integration) {
            throw IntegrationException::notFoundEnabledWithId($synchronization->getIntegrationId());
        }

        $this->fullSynchronizationContext->setFullSynchronization($synchronization->isFullSynchronization());
        if ($this->fullSynchronizationContext->isFullSynchronization()) {
            $integration->resetCursorProperties();
        }

        foreach ($this->synchronizers as $synchronizer) {
            $synchronizer->synchronize($integration);
        }
    }
}
