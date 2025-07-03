<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Collector;

use BitBag\SyliusErgonodePlugin\API\Client\AttributeApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;

final class DeletedAttributesCollector implements DeletedAttributesCollectorInterface
{
    private array $deletedAttributes = [];

    private ?string $attributeDeletedStreamCursorEnd = null;

    public function __construct(
        private readonly AttributeApiClientInterface $attributeApiClient,
    ) {
    }

    public function collect(IntegrationInterface $integration): void
    {
        $deletedAttributes = $this->attributeApiClient->getDeletedAttributes($integration, $this->attributeDeletedStreamCursorEnd);
        $stream = $deletedAttributes['data']['attributeDeletedStream'];
        foreach ($this->retrieveCategoryCodesFromResponse($stream) as $code) {
            $this->deletedAttributes[] = $code;
        }

        $this->attributeDeletedStreamCursorEnd = $stream['pageInfo']['endCursor'];

        if ($stream['pageInfo']['hasNextPage']) {
            $this->collect($integration);
        }
    }

    public function isInDeletedList(string $attributeCode): bool
    {
        return in_array($attributeCode, $this->deletedAttributes);
    }

    public function getDeletedList(): array
    {
        return $this->deletedAttributes;
    }

    public function getCursorEnd(): string
    {
        return $this->attributeDeletedStreamCursorEnd;
    }

    private function retrieveCategoryCodesFromResponse(array $stream): array
    {
        return array_map(fn (array $attribute) => $attribute['node'], $stream['edges']);
    }
}
