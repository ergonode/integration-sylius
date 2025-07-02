<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Assigner\Product;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ProductAttributeValueAssigner implements ProductAttributeValueAssignerInterface
{
    public function __construct(
        private readonly RepositoryInterface $productAttributeValueRepository,
        private readonly RepositoryInterface $attributeRepository,
        private readonly FactoryInterface $productAttributeValueFactory,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function assign(ProductInterface $product, array $productAttributeValue, string $attributeCode): void
    {
        foreach ($productAttributeValue as $language => $value) {
            $attribute = $this->attributeRepository->findOneBy(['code' => $attributeCode]);
            if (!$attribute instanceof AttributeInterface) {
                return;
            }

            $attributeValue = $this->productAttributeValueRepository->findOneBy([
                'subject' => $product,
                'attribute' => $attribute,
                'localeCode' => $language,
            ]);

            if (!$attributeValue instanceof ProductAttributeValueInterface) {
                /** @var ProductAttributeValueInterface $attributeValue */
                $attributeValue = $this->productAttributeValueFactory->createNew();
                $attributeValue->setAttribute($attribute);
                $attributeValue->setLocaleCode($language);
                $attributeValue->setProduct($product);
            }

            $attributeValue->setValue($value);

            $storageType = $attribute->getStorageType();

            switch ($storageType) {

            }

            $this->entityManager->persist($attributeValue);
        }
    }
}
