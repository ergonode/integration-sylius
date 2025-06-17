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

final class CategoryTreeLeafCodes extends ErgonodeRequestModel
{
    public function __construct(
        private readonly string $categoryTreeCode,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
        query GetCategoryTreeLeafCodes($treeCode: CategoryTreeCode!) {
          categoryTree(code: $treeCode) {
            code
            categoryTreeLeafList {
              edges {
                node {
                  category {
                    code
                    }
                    parentCategory {
                        code
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
            'treeCode' => $this->categoryTreeCode,
        ];
    }
}
