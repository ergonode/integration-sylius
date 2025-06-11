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
use BitBag\SyliusErgonodePlugin\Assigner\TaxonValuesAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Factory\DenormalizerFactoryInterface;
use BitBag\SyliusErgonodePlugin\Model\Category\Stream\CategoryNode;
use BitBag\SyliusErgonodePlugin\Provider\CategoryTreeLeafsCodesProviderInterface;
use BitBag\SyliusErgonodePlugin\Provider\TaxonProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CategorySynchronizer implements SynchronizerInterface
{
    public function __construct(
        private CategoryApiClientInterface $categoryApiClient,
        private CategoryTreeLeafsCodesProviderInterface $categoryTreeLeafsCodesProvider,
        private EntityManagerInterface $entityManager,
        private TaxonProviderInterface $taxonProvider,
        private TaxonValuesAssignerInterface $taxonValuesAssigner,
        private DenormalizerFactoryInterface $denormalizerFactory,
    ) {
    }

    public function synchronize(IntegrationInterface $integration): void
    {
        $categoryTreeLeafsCodes = $this->categoryTreeLeafsCodesProvider->provide($integration);
        $response = $this->categoryApiClient->getCategories($integration);

        $categoryStream = $response['data']['categoryStream'];
        $pageInfo = $categoryStream['pageInfo'];
        $categories = $categoryStream['edges'];
        $denormalizer = $this->denormalizerFactory->create();

        foreach ($categories as $category) {
            $nodeArray = $category['node'];
            if (false === in_array($nodeArray['code'], $categoryTreeLeafsCodes, true)) {
                continue;
            }

            /** @var CategoryNode $categoryNode */
            $categoryNode = $denormalizer->denormalize($nodeArray, CategoryNode::class, 'array');
            $taxon = $this->taxonProvider->provide($integration, $categoryNode->getCode());

            $this->taxonValuesAssigner->assign($categoryNode, $integration, $taxon);
            $this->entityManager->persist($taxon);
        }
        $endCursor = $pageInfo['endCursor'];
        $integration->setCategoryStreamCursorEnd($endCursor);

        if ($pageInfo['hasNextPage']) {
            $this->synchronize($integration);

            return;
        }

        $this->entityManager->flush();
    }
}
