<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Unit\Provider;

use BitBag\SyliusErgonodePlugin\API\Client\CategoryApiClientInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use BitBag\SyliusErgonodePlugin\Exception\CategorySynchronizationException;
use BitBag\SyliusErgonodePlugin\Provider\CategoryTreeLeafsCodesProvider;
use PHPUnit\Framework\TestCase;

final class CategoryTreeLeafsCodesProviderTest extends TestCase
{
    public function test_provide_returns_category_codes_and_parents(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getCategoryTreeCode')->willReturn('default');

        $categoryApiClient = $this->createMock(CategoryApiClientInterface::class);

        $categoryApiClient->method('getCategoryTreeLeafCodes')
            ->with($integration)
            ->willReturn([
                'data' => [
                    'categoryTree' => [
                        'code' => 'default',
                        'categoryTreeLeafList' => [
                            'edges' => [
                                [
                                    'node' => [
                                        'category' => ['code' => 'cat-1'],
                                        'parentCategory' => ['code' => 'parent-1'],
                                    ],
                                ],
                                [
                                    'node' => [
                                        'category' => ['code' => 'cat-2'],
                                        'parentCategory' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $provider = new CategoryTreeLeafsCodesProvider($categoryApiClient);
        $result = $provider->provide($integration);

        $this->assertSame(['cat-1', 'cat-2'], $result['codes']);
        $this->assertSame(['cat-1' => 'parent-1'], $result['parents']);
    }

    public function test_provide_throws_if_category_tree_code_mismatch(): void
    {
        $this->expectException(CategorySynchronizationException::class);
        $this->expectExceptionMessage('Invalid category tree found. Expected: `expected-tree`, actual: `mismatched-tree`.');

        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getCategoryTreeCode')->willReturn('expected-tree');

        $categoryApiClient = $this->createMock(CategoryApiClientInterface::class);
        $categoryApiClient->method('getCategoryTreeLeafCodes')->willReturn([
            'data' => [
                'categoryTree' => [
                    'code' => 'mismatched-tree',
                    'categoryTreeLeafList' => [
                        'edges' => []
                    ],
                ],
            ],
        ]);

        $provider = new CategoryTreeLeafsCodesProvider($categoryApiClient);
        $provider->provide($integration);
    }
}
