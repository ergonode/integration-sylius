<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\CLI;

use BitBag\SyliusErgonodePlugin\Message\Synchronization;
use BitBag\SyliusErgonodePlugin\Repository\IntegrationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'bitbag-sylius-ergonode:synchronize',
    description: 'Synchronize Sylius with Ergonode',
)]
final class SynchronizationCommand extends Command
{
    public function __construct(
        private readonly IntegrationRepositoryInterface $integrationRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('integrationCode', InputArgument::REQUIRED, 'Ergonode integration code');
        $this->addOption('full', 'f', InputOption::VALUE_NONE, 'Initiates full synchronization');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $integrationCode = $input->getArgument('integrationCode');
        $fullSync = $input->getOption('full');

        if (null === $integrationCode) {
            $message = 'Please provide integration code';
            $output->writeln($message);
            $this->logger->error($message);

            return -1;
        }

        $integration = $this->integrationRepository->findOneEnabledByCode((string) $integrationCode);
        if (null === $integration) {
            $message = sprintf('Integration with code %s could not be found', $integrationCode);
            $this->logger->error($message);
            $output->writeln($message);

            return -1;
        }

        /** @var int $integrationId */
        $integrationId = $integration->getId();

        $message = sprintf(
            '%s for integration with id `%s` initiated',
            true === $fullSync ? 'Full synchronization' : 'Synchronization',
            $integrationId,
        );
        $output->writeln($message);
        $this->logger->info($message);

        $this->messageBus->dispatch(new Synchronization($integrationId, $fullSync));

        return 1;
    }
}
