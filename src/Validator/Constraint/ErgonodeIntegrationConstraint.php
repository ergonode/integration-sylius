<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class ErgonodeIntegrationConstraint extends Constraint
{
    public string $authorizationException = 'bitbag_ergonode.integration.authorization_exception';

    public string $ergonodeErrorOccurred = 'bitbag_ergonode.integration.ergonode_error_occurred';

    public string $unrecognizedErrorOccurred = 'bitbag_ergonode.integration.unrecognized_error_occurred';

    public string $missingApiKey = 'bitbag_ergonode.integration.missing_api_key';

    public function validatedBy(): string
    {
        return 'bitbag_ergonode_integration_validator';
    }


    /**
     * @return 'class'
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
