<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Controller;

use BitBag\SyliusErgonodePlugin\Exception\IntegrationException;
use BitBag\SyliusErgonodePlugin\Message\Synchronization;
use BitBag\SyliusErgonodePlugin\Repository\IntegrationRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class SynchronizationAction
{
    public function __construct(
        private IntegrationRepositoryInterface $integrationRepository,
        private MessageBusInterface $messageBus,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $integration = $this->integrationRepository->findOneEnabledById($id);
        if (null === $integration) {
            throw IntegrationException::notFoundEnabledWithId($id);
        }

        $this->messageBus->dispatch(new Synchronization($id));

        return new RedirectResponse($this->router->generate('bitbag_ergonode_admin_integration_index'));
    }
}
