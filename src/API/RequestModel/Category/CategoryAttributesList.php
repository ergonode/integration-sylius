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
                      attribute {
                        code
                        name {
                          language
                          value
                        }
                      }
                      translations {
                        language

                        ... on TextAttributeValueTranslation {
                          textValue: value
                        }

                        ... on TextareaAttributeValueTranslation {
                          textareaValue: value
                          rawValue
                        }

                        ... on NumericAttributeValueTranslation {
                          numericValue: value
                        }

                        ... on PriceAttributeValueTranslation {
                          priceValue: value
                        }

                        ... on UnitAttributeValueTranslation {
                          unitValue: value
                        }

                        ... on DateAttributeValueTranslation {
                          dateValue: value
                        }

                        ... on SelectAttributeValueTranslation {
                          selectValue: value {
                            code
                            name {
                              language
                              value
                            }
                          }
                          translatedOption: translatedValue {
                            code
                            name
                          }
                        }

                        ... on MultiSelectAttributeValueTranslation {
                          multiSelectValue: value {
                            code
                            name {
                              language
                              value
                            }
                          }
                          translatedOptions: translatedValue {
                            code
                            name
                          }
                        }

                        ... on ImageAttributeValueTranslation {
                          imageValue: value {
                            url
                            name
                            extension
                            mime
                            size
                          }
                        }

                        ... on GalleryAttributeValueTranslation {
                          galleryValue: value {
                            url
                            name
                            extension
                            mime
                            size
                          }
                        }

                        ... on FileAttributeValueTranslation {
                          fileValue: value {
                            url
                            name
                            extension
                            mime
                            size
                          }
                        }

                        ... on ProductRelationAttributeValueTranslation {
                          productRelationValue: value {
                            sku
                          }
                        }
                      }
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
            'code' => $this->code,
        ];
    }
}
