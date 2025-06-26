<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\PropertyValueAssigner\Attribute;

use BitBag\SyliusErgonodePlugin\API\Client\AttributeApiClientInterface;
use BitBag\SyliusErgonodePlugin\Context\SynchronizationContextInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\AttributeSynchronizationException;
use BitBag\SyliusErgonodePlugin\Resolver\AttributeOption\AttributeOptionNameResolverInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;

final class AttributeOptionsAssigner implements AttributeOptionsAssignerInterface
{
    private const ATTRIBUTE_OPTION_ID_FORMAT = 'ergonode-attribute-option-%s';

    private const ATTRIBUTE_OPTION_END_CURSOR = 'ergonode-attribute-option-end-cursor';

    public function __construct(
        private readonly AttributeApiClientInterface $attributeApiClient,
        private readonly AttributeOptionNameResolverInterface $attributeOptionNameResolver,
        private readonly SynchronizationContextInterface $fullSynchronizationContext,
    ) {
    }

    public function assign(IntegrationInterface $integration, AttributeInterface $attribute, string $ergonodeAttributeCode, array $optionList): void
    {
        $configuration = $attribute->getConfiguration();
        $fullSync = $this->fullSynchronizationContext->isFullSynchronization();

        if (!$fullSync) {
            $choices = array_key_exists('choices', $configuration) ? $configuration['choices'] : [];
            $choices = $this->resolveOptions($optionList['edges'], $integration, $choices);
            $pageInfo = $optionList['pageInfo'];
            $cursor = $pageInfo['endCursor'];
            $hasNextPage = $pageInfo['hasNextPage'];
        } else {
            $choices = [];
            $cursor = null;
            $hasNextPage = true;
        }

        while ($hasNextPage) {
            $optionList = $this->getAttributeOptions($integration, $ergonodeAttributeCode, $cursor);

            $choices = $this->resolveOptions($optionList['edges'], $integration, $choices);

            $pageInfo = $optionList['pageInfo'];
            $cursor = $pageInfo['endCursor'];
            $hasNextPage = $pageInfo['hasNextPage'];
        }

        $configuration['choices'] = $choices;
        $configuration[self::ATTRIBUTE_OPTION_END_CURSOR] = $cursor;

        $attribute->setConfiguration($configuration);
    }

    public function resolveOptions(mixed $attributeOptions, IntegrationInterface $integration, array $options): array
    {
        foreach ($attributeOptions as $option) {
            $optionNode = $option['node'];
            $optionCode = $optionNode['code'];
            $index = sprintf(self::ATTRIBUTE_OPTION_ID_FORMAT, $optionCode);
            $options[$index] = $this->attributeOptionNameResolver->resolve($optionNode['name'], $integration);
        }

        return $options;
    }

    private function getAttributeOptions(IntegrationInterface $integration, string $ergonodeAttributeCode, ?string $after): array
    {
        $optionsResponse = $this->attributeApiClient->getSelectAttributeOptions(
            $integration,
            $ergonodeAttributeCode,
            $after,
        );

        if (array_key_exists('data', $optionsResponse) &&
            array_key_exists('attribute', $optionsResponse['data']) &&
            [] === $optionsResponse['data']['attribute']
        ) {
            throw AttributeSynchronizationException::attributeDoesNotSupportList($ergonodeAttributeCode);
        }

        if (!isset($optionsResponse['data']['attribute']['optionList'])) {
            throw AttributeSynchronizationException::invalidArrayStructure($optionsResponse, 'attribute.optionList');
        }

        return $optionsResponse['data']['attribute']['optionList'];
    }
}
