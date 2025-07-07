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
use BitBag\SyliusErgonodePlugin\Collector\DeletedAttributesCollectorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\AttributeProviderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
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
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly AttributeValuesAssignerInterface $attributeValuesAssigner,
        private readonly DeletedAttributesCollectorInterface $deletedAttributesCollector,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $this->deletedAttributesCollector->collect($integration);
        $attributeStream = $this->getAttributeStream($integration);
        if (null === $attributeStream) {
            return;
        }
        foreach ($attributeStream['edges'] as $attributeEdge) {
            $attributeNode = $attributeEdge['node'];

            if ([] === $attributeNode) {
                continue;
            }

            $code = $attributeNode['code'];
            if ($this->deletedAttributesCollector->isInDeletedList($code)) {
                continue;
            }

            $attribute = $this->attributeProvider->provide($integration, $code);

            try {
                $this->attributeValuesAssigner->assign($integration, $attribute, $attributeNode);
            } catch (AttributeSynchronizationException $exception) {
                $this->logger->error($exception->getMessage(), [$exception]);
                $this->logger->info(sprintf('Attribute `%s` could not have been synchronized.', $attribute->getCode()));

                continue;
            } catch (\Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Unexpected exception occurred during attribute synchronization, message: %s',
                        $exception->getMessage(),
                    ),
                    [$exception],
                );

                continue;
            }
            $this->logger->info(sprintf('Attribute `%s` has been synchronized and will be persisted.', $attribute->getCode()));
            $this->entityManager->persist($attribute);
        }

        $this->entityManager->persist($integration);
        $this->entityManager->flush();
        $this->logger->info(sprintf('Previously mentioned attributes have been saved.'));

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
        $attributeCodes = [];

        foreach ($this->deletedAttributesCollector->getDeletedList() as $code) {
            $resourceRelationMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue($integration, $code, RelationType::ATTRIBUTE_EXTERNAL_ID);
            if (null === $resourceRelationMapping) {
                $attributeCodes[] = $code;

                continue;
            }

            $attributeCodes[] = $resourceRelationMapping->getSyliusValue();
        }

        $attributes = $this->productAttributeRepository->findBy(['code' => $attributeCodes]);
        foreach ($attributes as $attribute) {
            $this->entityManager->remove($attribute);
        }

        $integration->setAttributeDeletedStreamCursorEnd($this->deletedAttributesCollector->getCursorEnd());

        $this->entityManager->persist($integration);
        $this->entityManager->flush();
    }
}
