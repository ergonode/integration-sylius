<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\RequestModel\Attribute;

use BitBag\SyliusErgonodePlugin\API\RequestModel\ErgonodeRequestModel;

final class SelectAttributeOptions extends ErgonodeRequestModel
{
    public function __construct(
        private readonly string $attributeCode,
        private readonly int $limit,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
        query SelectAttributeOptions($attributeCode: AttributeCode!, $previousCursorEnd: String) {
          attribute(code: $attributeCode) {
            ... on MultiSelectAttribute {
              optionList(first: 200, after: $previousCursorEnd) {
                edges {
                  node {
                    code
                    name {
                      language
                      value
                    }
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
              }
            }
            ... on SelectAttribute {
              optionList(first: 200, after: $after) {
                edges {
                  node {
                    code
                    name {
                      language
                      value
                    }
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
              }
            }
          }
        }
        GRAPHQL;
    }

    public function getRequestPayload(): array
    {
        return [
            'limit' => $this->limit,
            'previousCursorEnd' => $this->previousCursorEnd,
            'attributeCode' => $this->attributeCode,
        ];
    }
}
