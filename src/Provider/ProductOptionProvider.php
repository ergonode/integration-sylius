<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Provider;

use BitBag\SyliusErgonodePlugin\Assigner\Product\ProductOptionNameAssignerInterface;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ProductOptionProvider implements ProductOptionProviderInterface
{
    public function __construct(
        private readonly ProductOptionRepositoryInterface $productOptionRepository,
        private readonly FactoryInterface $productOptionFactory,
        private readonly ProductOptionNameAssignerInterface $productOptionNameAssigner,
    ) {
    }

    public function provide(array $bindingAttribute, IntegrationInterface $integration): ProductOptionInterface
    {
        /** @var ?ProductOptionInterface $option */
        $option = $this->productOptionRepository->findOneBy(['code' => $bindingAttribute['code']]);

        if (null !== $option) {
            return $option;
        }
        /** @var ProductOptionInterface $option */
        $option = $this->productOptionFactory->createNew();
        $option->setCode($bindingAttribute['code']);
        $this->productOptionNameAssigner->assign($integration, $option, $bindingAttribute['attribute']['name'] ?? []);

        return $option;
    }
}
