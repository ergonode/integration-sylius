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

final class CategoryDeletedStream extends ErgonodeRequestModel
{
    public function __construct(
        private readonly int $limit,
        private readonly ?string $previousCursorEnd = null,
    ) {
    }

    public function getRequestBody(): string
    {
        return <<<'GRAPHQL'
            query CategoryDeletedStream($previousCursorEnd: String, $limit: Int) {
              categoryDeletedStream(first: $limit, after: $previousCursorEnd) {
                pageInfo {
                  endCursor
                  hasNextPage
                }
                edges {
                  cursor
                  node
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
