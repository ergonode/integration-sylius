<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Mapping\Type;

use Sylius\Bundle\AdminBundle\Form\Type\ProductAutocompleteType;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

final class ProductExternalMappingType extends AbstractType
{
    /** @param ProductRepositoryInterface<ProductInterface> $productRepository */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('syliusValue', ProductAutocompleteType::class, [
                'label' => 'bitbag_ergonode.ui.sylius_value',
            ])
            ->add('ergonodeValue', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.ergonode_value',
            ])
        ;
        $builder
            ->get('syliusValue')->addModelTransformer(
                new ReversedTransformer(new ResourceToIdentifierTransformer($this->productRepository, 'code')),
            )
        ;
    }
}
