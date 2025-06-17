<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\Client;

use BitBag\SyliusErgonodePlugin\API\RequestModel\ErgonodeRequestModel;
use BitBag\SyliusErgonodePlugin\Encryptor\EncryptorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\ErgonodeAuthorizationException;
use BitBag\SyliusErgonodePlugin\Exception\ErgonodeRequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract readonly class AbstractErgonodeClient
{
    private const ERGONODE_GRAPHQL_API_ENDPOINT = 'api/graphql/';

    public function __construct(
        private EncryptorInterface $encryptor,
        private HttpClientInterface $ergonodeClient,
        private LoggerInterface $logger,
    ) {
    }

    public function query(ErgonodeRequestModel $model, IntegrationInterface $integration): array
    {
        try {
            $apiUrl = $this->getApiUrl($integration);
            $apiKey = $this->encryptor->decrypt($integration->getApiKey());
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Error during api credentials retrieval: %s', $exception->getMessage()), [
                'query' => $model->getRequestBody(),
                'variables' => $model->getRequestPayload(),
            ]);

            throw new \RuntimeException('Error during api credentials retrieval', 500, $exception);
        }

        try {
            $response = $this->ergonodeClient->request(Request::METHOD_POST, $apiUrl, [
                'json' => [
                    'query' => $model->getRequestBody(),
                    'variables' => $model->getRequestPayload(),
                ],
                'headers' => ['X-API-KEY' => $apiKey],
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Something went wrong while trying to fetch query: %s', $exception->getMessage()), [
                'query' => $model->getRequestBody(),
                'variables' => $model->getRequestPayload(),
            ]);

            throw ErgonodeRequestException::requestFailed($exception);
        }

        try {
            $responseArray = $response->toArray();

            if ($this->isAuthorizationException($responseArray)) {
                throw ErgonodeAuthorizationException::invalidCredentials();
            }

            return $responseArray;
        } catch (ErgonodeAuthorizationException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error(
                sprintf(
                    'Error decoding response from Ergonode: %s',
                    $exception->getMessage(),
                ),
            );

            throw ErgonodeRequestException::requestFailed($exception);
        }
    }

    private function getApiUrl(IntegrationInterface $configuration): string
    {
        if (null === $configuration->getApiUrl()) {
            throw ErgonodeAuthorizationException::missingApiUrl();
        }

        return sprintf('%s/%s', rtrim($configuration->getApiUrl(), '/'), self::ERGONODE_GRAPHQL_API_ENDPOINT);
    }

    public function isAuthorizationException(array $responseArray): bool
    {
        return array_key_exists('errors', $responseArray) &&
            array_key_exists('extensions', $responseArray['errors'][0]) &&
            array_key_exists('code', $responseArray['errors'][0]['extensions']) &&
            'UNAUTHENTICATED' === $responseArray['errors'][0]['extensions']['code'];
    }
}
