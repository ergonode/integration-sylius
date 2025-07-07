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

final class VariantCodesStream extends ErgonodeRequestModel
{
    public function __construct(
        private readonly int $limit,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
            query GetVariantSkus($limit: Int = 200, $previousCursorEnd: String) {
              productStream(first: $limit, after: $previousCursorEnd) {
                pageInfo {
                  endCursor
                  hasNextPage
                }
                edges {
                  node {
                    ... on VariableProduct {
                              sku
                              bindings {
                                code
                                __typename
                              }
                        variantList(first: 50) {
                            pageInfo {
                              endCursor
                              hasNextPage
                            }
                            edges {
                              node {
                                __typename
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
        ];
    }
}
