<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\ValidationRequestModel;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final readonly class ValidationApiClient extends AbstractErgonodeClient
{
    public function validate(IntegrationInterface $integration): array
    {
        return $this->query(new ValidationRequestModel(), $integration);
    }
}
