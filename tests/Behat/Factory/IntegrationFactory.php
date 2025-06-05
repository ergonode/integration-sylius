<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Factory;

use BitBag\SyliusErgonodePlugin\Entity\Integration;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class IntegrationFactory implements IntegrationFactoryInterface
{
    public function createNew(): IntegrationInterface
    {
        $integration =  new Integration();
        $integration->setEnabled(false);
        $integration->setApiUrl('http://127.0.0.1');
        $integration->setApiKey('1234567890');
        $integration->setCategoryTreeCode('1234567890');
        $integration->setName('Integration');

        return $integration;
    }

    public function createNamed(string $name): IntegrationInterface
    {
        $integration = $this->createNew();
        $integration->setName($name);

        return $integration;
    }
}
