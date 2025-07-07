<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\API\RequestModel\Category;

use BitBag\SyliusErgonodePlugin\API\RequestModel\ErgonodeRequestModel;

final class CategoryAttributesList extends ErgonodeRequestModel
{
    public function __construct(
        private readonly int $limit,
        private readonly ?string $code = null,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
            query GetCategoryAttributes($code: CategoryCode!, $limit: Int = 50, $previousCursorEnd: String) {
              category(code: $code) {
                code
                name {
                  language
                  value
                }

                attributeList(first: $limit, after: $previousCursorEnd) {
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
            'code' => $this->code,
        ];
    }
}
