<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\RequestModel\Product;

use BitBag\SyliusErgonodePlugin\API\RequestModel\ErgonodeRequestModel;

final class ProductStream extends ErgonodeRequestModel
{
    public function __construct(
        private readonly int $limit,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
             query GetProducts($first: Int = 50, $previousCursorEnd: String) {
              productStream(first: $first, after: $previousCursorEnd) {
                pageInfo {
                  endCursor
                  hasNextPage
                }
                edges {
                  node {
                    typename: __typename
                    sku
                    createdAt
                    editedAt
                    template {
                      code
                    }
                    status {
                      language
                      value {
                        code
                        translatedName
                      }
                    }
                    categoryList(first: 200) {
                      edges {
                        node {
                          code
                          name {
                            language
                            value
                          }
                        }
                      }
                    }
                    attributeList(first: 100) {
                    pageInfo {
                        endCursor
                        hasNextPage
                    }
                      edges {
                        node {
                          ...AttributeValueFields
                        }
                      }
                    }
                ... on VariableProduct {
                  bindings {
                    code
                    name {
                      language
                      value
                    }
                  }
                      variantList(first: 50) {
                        edges {
                          node {
                            sku
                          }
                        }
                      }
                    }
                    }
                }
            }
        }
        fragment AttributeValueFields on AttributeValue {
            typename: __typename
            attribute {
                code
                name {
                    language
                    value
                }
            }

            ... on TextAttributeValue {
                textTranslations: translations {
                    language
                    value
                }
            }
            ... on SelectAttributeValue {
                selectTranslations: translations {
                    language
                    value {
                        code
                        name {
                            language
                            value
                        }
                    }
                    translatedValue {
                        code
                        name
                    }
                }
            }
            ... on TextareaAttributeValue {
                textareaTranslations: translations {
                    language
                    value
                }
            }
            ... on DateAttributeValue {
                dateTranslations: translations {
                    language
                    value
                }
            }
            ... on NumberAttributeValue {
                numberTranslations: translations {
                    language
                    value
                }
            }
            ... on UnitAttributeValue {
                unitTranslations: translations {
                    language
                    value
                }
                attribute {
                    unit {
                        name
                        symbol
                    }
                }
            }
            ... on ImageAttributeValue {
                imageTranslations: translations {
                    language
                    value {
                        name
                        url
                    }
                }
            }
            ... on GalleryAttributeValue {
                galleryTranslations: translations {
                    language
                    value {
                        name
                        url
                    }
                }
            }
            ... on FileAttributeValue {
                fileTranslations: translations {
                    language
                    value {
                        name
                        url
                    }
                }
            }
            ... on MultiSelectAttributeValue {
                    multiSelectTranslations: translations {
                            language
                            value {
                            code
                                name {
                                language
                                    value
                            }
                            }
                            translatedValue {
                            code
                            name
                    }
                }
            }
            ... on ProductRelationAttributeValue {
                relationTranslations: translations {
                language
                value {
                    sku
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
        ];
    }
}
