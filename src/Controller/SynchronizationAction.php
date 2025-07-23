<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Controller;

use BitBag\SyliusErgonodePlugin\Message\Synchronization;
use BitBag\SyliusErgonodePlugin\Repository\IntegrationRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class SynchronizationAction
{
    private const FULL_SYNC_ROUTE = 'bitbag_ergonode_admin_integration_full_synchronization';

    public function __construct(
        private IntegrationRepositoryInterface $integrationRepository,
        private MessageBusInterface $messageBus,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();
        $integration = $this->integrationRepository->findOneEnabledById($id);

        if (null === $integration) {
            $session->getFlashBag()->add('error', 'The integration is not enabled.');
        }

        $routeName = $request->get('_route');
        $fullSync = self::FULL_SYNC_ROUTE === $routeName;

        $this->messageBus->dispatch(new Synchronization($id, $fullSync));
        $session->getFlashBag()->add(
            'success',
            sprintf(
                '%s has been started.',
                $fullSync ? 'Full synchronization' : 'Synchronization',
            ),
        );

        return new RedirectResponse($this->router->generate('bitbag_ergonode_admin_integration_index'));
    }
}
