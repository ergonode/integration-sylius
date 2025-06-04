<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Mapping\Type;

use Sylius\Bundle\AdminBundle\Form\Type\TaxonAutocompleteType;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

final class CategoryExternalMappingType extends AbstractType
{
    /**
     * @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository
     */
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('syliusValue', TaxonAutocompleteType::class, [
                'label' => 'bitbag_ergonode.ui.sylius_value',
            ])
            ->add('ergonodeValue', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.ergonode_value',
            ])
        ;

        $builder
            ->get('syliusValue')->addModelTransformer(
                new ReversedTransformer(new ResourceToIdentifierTransformer($this->taxonRepository, 'code')),
            )
        ;
    }
}
