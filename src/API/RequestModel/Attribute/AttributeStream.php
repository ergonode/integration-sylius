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

final class AttributeStream extends ErgonodeRequestModel
{
    public function __construct(
        private readonly int $limit,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
            query AttributeStream($previousCursorEnd: String, $first: Int = 40) {
              attributeStream(after: $previousCursorEnd, first: $first) {
                edges {
                  node {
                    typename: __typename
                    code
                    name {
                      language
                      value
                    }
                    scope
                    metadata {
                      key
                      value
                    }

                    ... on DateAttribute {
                      format
                    }

                    ... on PriceAttribute {
                      currency
                    }

                    ... on TextareaAttribute {
                      richEdit
                    }

                    ... on UnitAttribute {
                      unit {
                        name
                        symbol
                      }
                    }

                    ... on MultiSelectAttribute {
                      customFields {
                        code
                      }
                      optionList(first: 200) {
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
                      customFields {
                        code
                      }
                      optionList(first: 200) {
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
                  cursor
                }
                pageInfo {
                  endCursor
                  hasNextPage
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
        ];
    }
}
