<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Factory;

use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

interface IntegrationFactoryInterface
{
    public function createNew(): IntegrationInterface;

    public function createNamed(string $name): IntegrationInterface;
}
