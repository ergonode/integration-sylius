<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Validator;

use BitBag\SyliusErgonodePlugin\API\Client\AbstractErgonodeClient;
use BitBag\SyliusErgonodePlugin\API\RequestModel\ValidationRequestModel;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\ErgonodeAuthorizationException;
use BitBag\SyliusErgonodePlugin\Exception\ErgonodeRequestException;
use BitBag\SyliusErgonodePlugin\Validator\Constraint\ErgonodeIntegrationConstraint;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ErgonodeIntegrationValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AbstractErgonodeClient $ergonodeClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ErgonodeIntegrationConstraint::class);
        Assert::isInstanceOf($value, IntegrationInterface::class);

        if (false === $value->isEnabled()) {
            return;
        }

        if (null === $value->getApiKey()) {
            $this->context->buildViolation($constraint->missingApiKey)
                ->atPath('apiKey')
                ->addViolation()
            ;

            return;
        }

        if (null === $value->getApiUrl()) {
            return;
        }

        try {
            $this->ergonodeClient->query(new ValidationRequestModel(), $value);
        } catch (ErgonodeAuthorizationException) {
            $this->context->addViolation($constraint->authorizationException);
        } catch (ErgonodeRequestException $e) {
            $this->context->addViolation($constraint->ergonodeErrorOccurred);
            $this->logger->warning(sprintf('[BitBag/SyliusErgonodePlugin] Ergonode error during validation: %s', $e->getMessage()));
        } catch (\Throwable $e) {
            $this->context->addViolation($constraint->unrecognizedErrorOccurred);
            $this->logger->warning(sprintf('[BitBag/SyliusErgonodePlugin] Unrecognized error during validation: %s', $e->getMessage()));
        }
    }
}
