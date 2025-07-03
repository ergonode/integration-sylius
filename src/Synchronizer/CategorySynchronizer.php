<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Synchronizer;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Assigner\Taxon\TaxonParentAssignerInterface;
use BitBag\SyliusErgonodePlugin\Assigner\TaxonValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Collector\DeletedCategoriesCollectorInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use BitBag\SyliusErgonodePlugin\Exception\SynchronizationException;
use BitBag\SyliusErgonodePlugin\Factory\DenormalizerFactoryInterface;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Provider\CategoryTreeLeafsCodesProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\TaxonProviderInterface;
use BitBag\SyliusErgonodePlugin\Resolver\ResourceRelationMappingResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final class CategorySynchronizer implements SynchronizerInterface
{
    private array $taxons = [];

    public function __construct(
        private readonly CategoryApiClientInterface $categoryApiClient,
        private readonly CategoryTreeLeafsCodesProviderInterface $categoryTreeLeafsCodesProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly TaxonProviderInterface $taxonProvider,
        private readonly TaxonValuesAssignerInterface $taxonValuesAssigner,
        private readonly DenormalizerFactoryInterface $denormalizerFactory,
        private readonly RepositoryInterface $taxonRepository,
        private readonly ResourceRelationMappingResolverInterface $resourceRelationMappingResolver,
        private readonly TaxonParentAssignerInterface $taxonParentAssigner,
        private readonly DeletedCategoriesCollectorInterface $deletedCategoriesCollector,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $categoryTree = $this->categoryTreeLeafsCodesProvider->provide($integration);
        $categoryTreeLeafsCodes = $categoryTree['codes'];
        $parentCodes = $categoryTree['parents'];

        $this->deletedCategoriesCollector->collect($integration);
        $response = $this->categoryApiClient->getCategories($integration);

        $categoryStream = $response['data']['categoryStream'];
        $pageInfo = $categoryStream['pageInfo'];
        $categories = $categoryStream['edges'];
        $denormalizer = $this->denormalizerFactory->create();


        foreach ($categories as $category) {
            if ([] === $category['node']) {
                continue;
            }

            $nodeArray = $category['node'];
            if (false === in_array($nodeArray['code'], $categoryTreeLeafsCodes, true)
                || $this->deletedCategoriesCollector->isInDeletedList($category['node']['code'])
            ) {
                continue;
            }

            /** @var CategoryNode $categoryNode */
            $categoryNode = $denormalizer->denormalize($nodeArray, CategoryNode::class, 'array');
            $taxon = $this->taxonProvider->provide($integration, $categoryNode->getCode());

            try {
                $this->taxonValuesAssigner->assign($categoryNode, $integration, $taxon);
                $this->taxons[$categoryNode->getCode()] = $taxon;
            } catch (SynchronizationException $exception) {
                $this->logger->error($exception->getMessage(), [$exception]);
                $this->logger->info(sprintf('Taxon `%s` could not have been synchronized.', $taxon->getCode()));

                continue;
            }

            $this->logger->info(sprintf('Taxon `%s` has been synchronized and will be persisted.', $taxon->getCode()));
            $this->entityManager->persist($taxon);
        }

        foreach ($this->taxons as $taxonCode => $taxon) {
            if (!array_key_exists($taxonCode, $parentCodes) || !is_string($parentCodes[$taxonCode])) {
                continue;
            }

            if (array_key_exists($parentCodes[$taxonCode], $this->taxons)) {
                $taxon->setParent($this->taxons[$parentCodes[$taxonCode]]);
            }

            $this->taxonParentAssigner->assign($taxon, $parentCodes[$taxonCode]);
        }

        $endCursor = $pageInfo['endCursor'];
        if (null !== $endCursor) {
            $integration->setCategoryStreamCursorEnd($endCursor);
        }

        $this->entityManager->persist($integration);
        $this->entityManager->flush();

        $this->logger->info(sprintf('Previously mentioned categories have been saved.'));

        if ($pageInfo['hasNextPage']) {
            $this->synchronize($integration);

            return;
        }

        $this->synchronizeDeletedCategories($integration);
    }

    public function synchronizeDeletedCategories(IntegrationInterface $integration): void
    {
        foreach ($this->deletedCategoriesCollector->getDeletedList() as $code) {
            $resourceRelationMapping = $this->resourceRelationMappingResolver->resolveForErgonodeValue($integration, $code, RelationType::CATEGORY_EXTERNAL_ID);
            if (null === $resourceRelationMapping) {
                $categoryCodes[] = $code;

                continue;
            }

            $categoryCodes[] = $resourceRelationMapping->getSyliusValue();
        }
        $categories = $this->taxonRepository->findBy(['code' => $categoryCodes]);

        foreach ($categories as $category) {
            $this->entityManager->remove($category);
        }

        $integration->setCategoryDeletedStreamCursorEnd($this->deletedCategoriesCollector->getCursorEnd());

        $this->entityManager->persist($integration);
        $this->entityManager->flush();
    }
}
