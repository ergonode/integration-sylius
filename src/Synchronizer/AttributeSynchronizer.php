<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Synchronizer;

use BitBag\SyliusErgonodePlugin\API\Client\AttributeApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\AttributeValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\AttributeProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Repository\ProductAttributeRepositoryInterface;

final class AttributeSynchronizer implements SynchronizerInterface
{
    public function __construct(
        private readonly AttributeApiClientInterface $attributeApiClient,
        private readonly AttributeProviderInterface $attributeProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly AttributeValuesAssignerInterface $attributeValuesAssigner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $attributeStream = $this->getAttributeStream($integration);
        if (null === $attributeStream) {
            return;
        }
        foreach ($attributeStream['edges'] as $attributeEdge) {
            $attributeNode = $attributeEdge['node'];
            $attribute = $this->attributeProvider->provide($integration, $attributeNode['code']);

            try {
                $this->attributeValuesAssigner->assign($integration, $attribute, $attributeNode);
            } catch (AttributeSynchronizationException $exception) {
                $this->logger->error($exception->getMessage());

                continue;
            } catch (\Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Unexpected exception occurred during attribute synchronization, message: %s',
                        $exception->getMessage(),
                    ),
                );

                continue;
            }

            $this->entityManager->persist($attribute);
        }

        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        if (false !== $attributeStream['pageInfo']['hasNextPage'] &&
            null !== $attributeStream['pageInfo']['endCursor']
        ) {
            $integration->setAttributeStreamCursorEnd($attributeStream['pageInfo']['endCursor']);
            $this->synchronize($integration);
        }

        $this->synchronizeDeletedAttributes($integration);
    }

    private function getAttributeStream(IntegrationInterface $integration): ?array
    {
        $attributes = $this->attributeApiClient->getAttributes($integration);

        if (
            !array_key_exists('data', $attributes) ||
            !is_array($attributes['data']) ||
            !array_key_exists('attributeStream', $attributes['data']) ||
            !is_array($attributes['data']['attributeStream'])
        ) {
            return null;
        }

        return $attributes['data']['attributeStream'];
    }

    public function synchronizeDeletedAttributes(IntegrationInterface $integration): void
    {
        $deletedCategories = $this->attributeApiClient->getDeletedAttributes($integration);
        $stream = $deletedCategories['data']['attributeDeletedStream'];

        $categories = $this->productAttributeRepository->findBy(['code' => $this->retrieveCodesFromResponse($stream)]);

        foreach ($categories as $category) {
            $this->entityManager->remove($category);
        }

        if (null !== $stream['pageInfo']['endCursor']) {
            $integration->setAttributeDeletedStreamCursorEnd($stream['pageInfo']['endCursor']);
        }

        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        if (false !== $stream['pageInfo']['hasNextPage']) {
            $this->synchronizeDeletedAttributes($integration);
        }
    }

    private function retrieveCodesFromResponse(array $stream): array
    {
        return array_map(fn (array $attribute) => $attribute['node'], $stream['edges']);
    }
}
